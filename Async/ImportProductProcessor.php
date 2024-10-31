<?php

namespace Creativestyle\Bundle\AkeneoBundle\Async;

use Creativestyle\Bundle\AkeneoBundle\Async\Topic\ImportProductsTopic;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\CacheBundle\Provider\MemoryCacheProviderAwareInterface;
use Oro\Bundle\CacheBundle\Provider\MemoryCacheProviderAwareTrait;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Authentication\Token\IntegrationTokenAwareTrait;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Entity\FieldsChanges;
use Oro\Bundle\IntegrationBundle\Provider\SyncProcessorRegistry;
use Oro\Bundle\MessageQueueBundle\Entity\Job;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ImportProductProcessor implements
    MessageProcessorInterface,
    TopicSubscriberInterface,
    MemoryCacheProviderAwareInterface
{
    use IntegrationTokenAwareTrait;
    use MemoryCacheProviderAwareTrait;

    public function __construct(
        private DoctrineHelper $doctrineHelper,
        private JobRunner $jobRunner,
        private TokenStorageInterface $tokenStorage,
        private LoggerInterface $logger,
        private SyncProcessorRegistry $syncProcessorRegistry
    ) {
    }

    #[\Override]
    public static function getSubscribedTopics(): array
    {
        return [ImportProductsTopic::getName()];
    }

    #[\Override]
    public function process(MessageInterface $message, SessionInterface $session): string
    {
        $body = $message->getBody();

        /** @var EntityManagerInterface $em */
        $em = $this->doctrineHelper->getEntityManagerForClass(Integration::class);

        /** @var Integration $integration */
        $integration = $em->find(Integration::class, $body['integrationId']);

        if (!$integration) {
            $this->logger->error(
                sprintf('The integration not found: %s', $body['integrationId'])
            );

            return self::REJECT;
        }
        if (!$integration->isEnabled()) {
            $this->logger->error(
                sprintf('The integration is not enabled: %s', $body['integrationId'])
            );

            return self::REJECT;
        }

        $this->setTemporaryIntegrationToken($integration);

        $result = $this->jobRunner->runDelayed(
            $body['jobId'],
            function (JobRunner $jobRunner, Job $child) use ($integration, $body) {
                $this->doctrineHelper->refreshIncludingUnitializedRelations($integration);
                $processor = $this->syncProcessorRegistry->getProcessorForIntegration($integration);

                $em = $this->doctrineHelper->getEntityManager(FieldsChanges::class);
                /** @var FieldsChanges $fieldsChanges */
                $fieldsChanges = $em
                    ->getRepository(FieldsChanges::class)
                    ->findOneBy(['entityId' => $child->getId(), 'entityClass' => Job::class]);

                if (!$fieldsChanges) {
                    $this->logger->error(
                        sprintf('Source data from Akeneo not found for job: %s', $child->getId())
                    );

                    return false;
                }

                foreach ($fieldsChanges->getChangedFields() as $key => $changes) {
                    $this->memoryCacheProvider->get(
                        'akeneo_' . $key,
                        function () use (&$changes) {
                            return $changes;
                        }
                    );
                }

                $status = $processor->process(
                    $integration,
                    $body['connector'] ?? null,
                    $body['connector_parameters'] ?? []
                );

                $em->clear(FieldsChanges::class);

                return $status;
            }
        );

        return $result ? self::ACK : self::REJECT;
    }
}
