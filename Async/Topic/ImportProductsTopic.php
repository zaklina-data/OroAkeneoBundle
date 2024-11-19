<?php

namespace Creativestyle\Bundle\AkeneoBundle\Async\Topic;

use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportProductsTopic extends AbstractTopic
{
    private const string TOPIC = 'oro.integration.akeneo.product';

    public static function getName(): string
    {
        return self::TOPIC;
    }

    public static function getDescription(): string
    {
        return 'Akeneo product import.';
    }

    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined([
                'integrationId',
                'connector',
                'connector_parameters',
                'transport_batch_size'
            ])
            ->setRequired([
                'integrationId',
                'jobId'
            ])
            ->setDefaults([
                'connector' => null,
                'connector_parameters' => [],
            ])
            ->addAllowedTypes('integrationId', 'int')
            ->addAllowedTypes('connector', ['null', 'string'])
            ->addAllowedTypes('connector_parameters', 'array');
    }
}
