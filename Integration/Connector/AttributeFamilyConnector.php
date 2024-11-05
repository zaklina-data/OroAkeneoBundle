<?php

namespace Creativestyle\Bundle\AkeneoBundle\Integration\Connector;

use Creativestyle\Bundle\AkeneoBundle\Integration\AkeneoTransportInterface;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeFamily;
use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;

/**
 * @property AkeneoTransportInterface $transport
 */
class AttributeFamilyConnector extends AbstractConnector
{
    private const IMPORT_JOB_NAME = 'akeneo_attribute_family_import';

    #[\Override]
    public function getLabel(): string
    {
        return 'oro.akeneo.connector.attribute_family.label';
    }

    #[\Override]
    public function getImportEntityFQCN(): string
    {
        return AttributeFamily::class;
    }

    #[\Override]
    public function getImportJobName(): string
    {
        return self::IMPORT_JOB_NAME;
    }

    #[\Override]
    public function getType(): string
    {
        return 'attribute_family';
    }

    #[\Override]
    protected function getConnectorSource(): iterable
    {
        return $this->transport->getAttributeFamilies();
    }
}
