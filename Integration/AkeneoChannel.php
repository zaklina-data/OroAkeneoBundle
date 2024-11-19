<?php

namespace Creativestyle\Bundle\AkeneoBundle\Integration;

use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface;
use Oro\Bundle\IntegrationBundle\Provider\IconAwareIntegrationInterface;

class AkeneoChannel implements ChannelInterface, IconAwareIntegrationInterface
{
    public const TYPE = 'oro_akeneo';

    #[\Override]
    public function getLabel(): string
    {
        return 'oro.akeneo.integration.channel.label';
    }

    #[\Override]
    public function getIcon(): string
    {
        return 'bundles/oroakeneo/img/akeneo-icon.svg';
    }
}
