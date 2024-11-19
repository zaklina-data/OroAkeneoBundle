<?php

namespace Creativestyle\Bundle\AkeneoBundle\ImportExport\Writer;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\ManagerRegistry;
use Creativestyle\Bundle\AkeneoBundle\EventListener\AdditionalOptionalListenerManager;
use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\BatchBundle\Item\ItemWriterInterface;
use Oro\Bundle\BatchBundle\Item\Support\ClosableInterface;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Oro\Bundle\BatchBundle\Step\StepExecutionRestoreInterface;
use Oro\Bundle\EntityExtendBundle\EntityPropertyInfo;
use Oro\Bundle\PlatformBundle\Manager\OptionalListenerManager;

class CumulativeWriter implements
    ItemWriterInterface,
    ClosableInterface,
    StepExecutionAwareInterface,
    StepExecutionRestoreInterface
{
    private const MAX_UOW_OBJECTS_WITH_CHANGES = 150;
    private const MAX_UOW_OBJECTS_WITHOUT_CHANGES = 200;
    private const MAX_UOW_OPERATIONS = 100;
    private const LETS_WRITE = false;
    private const LETS_SKIP = true;

    private array $items = [];

    private array $forceListeners = [
        'oro_entity.event_listener.entity_modify_created_updated_properties_listener',
        'oro_redirect.event_listener.slug_change',
        'oro_redirect.event_listener.slug_prototype_change',
        'oro_website.indexation_request_listener',
        'oro_website_search.reindex_request.listener',
    ];

    public function __construct(
        private ItemWriterInterface $writer,
        private OptionalListenerManager $optionalListenerManager,
        private ManagerRegistry $registry,
        private AdditionalOptionalListenerManager $additionalOptionalListenerManager
    ) {
    }

    #[\Override]
    public function write(array $items): void
    {
        foreach ($items as $item) {
            $this->items[] = $item;
        }

        if ($this->skipFlush($items)) {
            return;
        }

        $this->doWrite();
    }

    private function doWrite(): void
    {
        try {
            $this->additionalOptionalListenerManager->disableListeners();
            $this->optionalListenerManager->disableListeners(
                $this->optionalListenerManager->getListeners()
            );
            foreach ($this->forceListeners as $forceListener) {
                try {
                    $this->optionalListenerManager->enableListener($forceListener);
                } catch (\InvalidArgumentException $e) {
                }
            }

            $this->writer->write($this->items);
        } finally {
            $this->items = [];

            $entityManager = $this->registry->getManager();
            $entityManager->flush();
            $entityManager->clear();

            $this->optionalListenerManager->enableListeners(
                $this->optionalListenerManager->getListeners()
            );
            $this->additionalOptionalListenerManager->enableListeners();
        }
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function skipFlush(array &$items): bool
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->registry->getManager();

        foreach ($items as $item) {
            $entityManager->persist($item);
        }

        $unitOfWork = $entityManager->getUnitOfWork();

        $count = $this->getUnitOfWorkChangesCount($unitOfWork);
        if ($count > self::MAX_UOW_OPERATIONS) {
            return self::LETS_WRITE;
        }

        $entityStates = $this->getUnitOfWorkStatesCount($unitOfWork);
        if ($entityStates > self::MAX_UOW_OBJECTS_WITHOUT_CHANGES) {
            return self::LETS_WRITE;
        }

        if ($count && $entityStates > self::MAX_UOW_OBJECTS_WITH_CHANGES) {
            return self::LETS_WRITE;
        }

        if (!$count) {
            return self::LETS_SKIP;
        }

        foreach ($items as $item) {
            $unitOfWork->computeChangeSet($entityManager->getClassMetadata(ClassUtils::getClass($item)), $item);
        }

        $count = $this->getUnitOfWorkChangesCount($unitOfWork);
        if ($count > self::MAX_UOW_OPERATIONS) {
            return self::LETS_WRITE;
        }

        $entityStates = $this->getUnitOfWorkStatesCount($unitOfWork);
        if ($entityStates > self::MAX_UOW_OBJECTS_WITHOUT_CHANGES) {
            return self::LETS_WRITE;
        }

        if ($count && $entityStates > self::MAX_UOW_OBJECTS_WITH_CHANGES) {
            return self::LETS_WRITE;
        }

        return self::LETS_SKIP;
    }

    private function getUnitOfWorkChangesCount(UnitOfWork $unitOfWork): int
    {
        return
            count($unitOfWork->getScheduledEntityInsertions()) +
            count($unitOfWork->getScheduledEntityUpdates()) +
            count($unitOfWork->getScheduledEntityDeletions()) +
            count($unitOfWork->getScheduledCollectionUpdates()) +
            count($unitOfWork->getScheduledCollectionDeletions());
    }

    private function getUnitOfWorkStatesCount(UnitOfWork $unitOfWork): int
    {
        $entityStates = \Closure::bind(
            function ($property) {
                return $this->{$property};
            },
            $unitOfWork,
            $unitOfWork
        )(
            'entityStates'
        );

        return count($entityStates);
    }

    #[\Override]
    public function setStepExecution(StepExecution $stepExecution): void
    {
        if ($this->writer instanceof StepExecutionAwareInterface) {
            $this->writer->setStepExecution($stepExecution);
        }
    }

    #[\Override]
    public function restoreStepExecution()
    {
        if ($this->writer instanceof StepExecutionRestoreInterface) {
            $this->writer->restoreStepExecution();
        }
    }

    /** {@inheritdoc} */
    public function close(): void
    {
        $this->doWrite();

        if ($this->writer instanceof ClosableInterface) {
            $this->writer->close();
        }
    }

    public function initialize(): void
    {
        if (EntityPropertyInfo::methodExists($this->writer, 'initialize')) {
            $this->writer->initialize();
        }
    }

    public function flush(): void
    {
        if (EntityPropertyInfo::methodExists($this->writer, 'flush')) {
            $this->writer->flush();
        }
    }
}
