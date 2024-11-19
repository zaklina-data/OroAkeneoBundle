<?php

namespace Creativestyle\Bundle\AkeneoBundle\ImportExport\EventListener;

use Creativestyle\Bundle\AkeneoBundle\ImportExport\Strategy\DefaultOwnerHelper;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Event\StrategyEvent;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

class OwnerStrategyEventListener
{
    private ?Channel $channel = null;

    public function __construct(
        private DoctrineHelper $doctrineHelper,
        private DefaultOwnerHelper $defaultOwnerHelper
    ) {
    }

    public function onProcessBefore(StrategyEvent $event): void
    {
        if (!$this->channel && !$this->getChannel($event->getContext())) {
            return;
        }

        $this->defaultOwnerHelper->populateChannelOwner($event->getEntity(), $this->channel);
    }

    public function onProcessAfter(StrategyEvent $event): void
    {
        if (!$this->channel && !$this->getChannel($event->getContext())) {
            return;
        }

        $this->defaultOwnerHelper->populateChannelOwner($event->getEntity(), $this->channel);
    }

    protected function getChannel(ContextInterface $context): Channel
    {
        if (!$this->channel && $context->getOption('channel')) {
            $this->channel = $this->doctrineHelper->getEntityReference(
                Channel::class,
                $context->getOption('channel')
            );
        }

        return $this->channel;
    }

    public function onClear(): void
    {
        $this->channel = null;
    }
}
