<?php

namespace Creativestyle\Bundle\AkeneoBundle\Integration\Connector;

use Creativestyle\Bundle\AkeneoBundle\Placeholder\SchemaUpdateFilter;
use Oro\Bundle\CacheBundle\Provider\MemoryCacheProviderAwareInterface;
use Oro\Bundle\CacheBundle\Provider\MemoryCacheProviderAwareTrait;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;
use Oro\Bundle\IntegrationBundle\Provider\AllowedConnectorInterface;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;
use Oro\Bundle\ProductBundle\Entity\Product;

/**
 * Integration product connector.
 */
class ProductConnector extends AbstractConnector implements AllowedConnectorInterface, MemoryCacheProviderAwareInterface
{
    use MemoryCacheProviderAwareTrait;

    private const IMPORT_JOB_NAME = 'akeneo_product_import';
    private const PAGE_SIZE = 100;
    private const TYPE = 'product';

    protected SchemaUpdateFilter $schemaUpdateFilter;

    #[\Override]
    public function getLabel(): string
    {
        return 'oro.akeneo.connector.product.label';
    }

    #[\Override]
    public function getImportEntityFQCN(): string
    {
        return Product::class;
    }

    #[\Override]
    public function getImportJobName(): string
    {
        return self::IMPORT_JOB_NAME;
    }

    #[\Override]
    public function getType(): string
    {
        return self::TYPE;
    }

    #[\Override]
    public function isAllowed(Channel $integration, array $processedConnectorsStatuses): bool
    {
        return !$this->needToUpdateSchema($integration);
    }

    public function setSchemaUpdateFilter(SchemaUpdateFilter $schemaUpdateFilter): void
    {
        $this->schemaUpdateFilter = $schemaUpdateFilter;
    }

    #[\Override]
    protected function getConnectorSource(): iterable
    {
        $items = $this->memoryCacheProvider->get('akeneo_items') ?? [];

        if ($items) {
            return new \ArrayIterator();
        }

        $iterator = new \AppendIterator();
        $iterator->append($this->transport->getProducts(self::PAGE_SIZE));
        $iterator->append($this->transport->getProductModels(self::PAGE_SIZE));

        return $iterator;
    }

    /**
     * Checks if schema is changed and need to update it.
     */
    private function needToUpdateSchema(Channel $integration): bool
    {
        return $this->schemaUpdateFilter->isApplicable($integration, Product::class);
    }
}
