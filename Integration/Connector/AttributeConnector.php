<?php

namespace Creativestyle\Bundle\AkeneoBundle\Integration\Connector;

use Creativestyle\Bundle\AkeneoBundle\Integration\AkeneoTransportInterface;
use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;

/**
 * @property AkeneoTransportInterface $transport
 */
class AttributeConnector extends AbstractConnector
{
    private const IMPORT_JOB_NAME = 'akeneo_attribute_import';
    private const PAGE_SIZE = 25;

    #[\Override]
    public function getLabel(): string
    {
        return 'oro.akeneo.connector.attribute.label';
    }

    #[\Override]
    public function getImportEntityFQCN(): string
    {
        return FieldConfigModel::class;
    }

    #[\Override]
    public function getImportJobName(): string
    {
        return self::IMPORT_JOB_NAME;
    }

    #[\Override]
    public function getType(): string
    {
        return 'attribute';
    }

    #[\Override]
    protected function getConnectorSource(): iterable
    {
        return $this->transport->getAttributes(self::PAGE_SIZE);
    }
}
