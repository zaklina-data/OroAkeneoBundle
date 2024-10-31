<?php

namespace Creativestyle\Bundle\AkeneoBundle\Async;

use Creativestyle\Bundle\AkeneoBundle\Integration\AkeneoChannel;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\IntegrationBundle\Async\Topic\SyncIntegrationTopic;
use Oro\Bundle\IntegrationBundle\Authentication\Token\IntegrationTokenAwareTrait;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Provider\LoggerStrategyAwareInterface;
use Oro\Bundle\IntegrationBundle\Provider\SyncProcessorRegistry;
use Oro\Bundle\MessageQueueBundle\Entity\Job;
use Oro\Bundle\MessageQueueBundle\Entity\Repository\JobRepository;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\Job as MessageJob;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Async processor to run integration processor
 * Add job details to connector parameters
 * @see \Oro\Bundle\IntegrationBundle\Async\SyncIntegrationProcessor
 */
class SyncIntegrationProcessor implements MessageProcessorInterface, ContainerAwareInterface, TopicSubscriberInterface
{
    use ContainerAwareTrait;
    use IntegrationTokenAwareTrait;

    public function __construct(
        private ManagerRegistry $doctrine,
        private TokenStorageInterface $tokenStorage,
        private SyncProcessorRegistry $syncProcessorRegistry,
        private JobRunner $jobRunner,
        private LoggerInterface $logger
    ) {
    }

    public static function getSubscribedTopics(): array
    {
        return [SyncIntegrationTopic::getName()];
    }

    public function process(MessageInterface $message, SessionInterface $session): string
    {
        /** @noinspection DuplicatedCode */
        $messageBody = $message->getBody();

        /** @var EntityManagerInterface $em */
        $em = $this->doctrine->getManager();

        /** @var Integration $integration */
        $integration = $em->find(Integration::class, $messageBody['integration_id']);
        if (!$integration || !$integration->isEnabled()) {
            $this->logger->critical('Integration should exist and be enabled');

            return self::REJECT;
        }

        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $this->setTemporaryIntegrationToken($integration);
        $integration->getTransport()->getSettingsBag()->set('page_size', $messageBody['transport_batch_size']);

        $jobName = $this->jobRunner->getJobNameByMessage($message);
        $ownerId = $message->getMessageId();

        $rootJob = $this->getJobRepository()->findRootJobByOwnerIdAndJobName($ownerId, $jobName);
        if (!$rootJob || $rootJob->getStatus() === MessageJob::STATUS_CANCELLED) {
            return self::REJECT;
        }

        $result = $this->jobRunner->runUnique(
            $ownerId,
            $jobName,
            function (JobRunner $jobRunner, Job $job) use ($integration, $messageBody) {
                $processor = $this->syncProcessorRegistry->getProcessorForIntegration($integration);
                if ($processor instanceof LoggerStrategyAwareInterface) {
                    $processor->getLoggerStrategy()->setLogger($this->logger);
                }
                // Customization starts
                $connectorParameters = $messageBody['connector_parameters'];
                if ($integration->getType() === AkeneoChannel::TYPE) {
                    $connectorParameters['rootJobId'] = $job->getRootJob()->getId();
                }
                // Customization ends

                return $processor->process(
                    $integration,
                    $messageBody['connector'],
                    $connectorParameters
                );
            }
        );

        return $result ? self::ACK : self::REJECT;
    }

    public function getJobRepository(): ObjectRepository|JobRepository
    {
        return $this->doctrine->getManagerForClass(Job::class)->getRepository(Job::class);
    }
}
