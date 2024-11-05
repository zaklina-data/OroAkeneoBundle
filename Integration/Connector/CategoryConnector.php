<?php

namespace Creativestyle\Bundle\AkeneoBundle\Integration\Connector;

use Creativestyle\Bundle\AkeneoBundle\Integration\AkeneoTransportInterface;
use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;

/**
 * @property AkeneoTransportInterface $transport
 */
class CategoryConnector extends AbstractConnector
{
    private const IMPORT_JOB_NAME = 'akeneo_category_import';
    private const PAGE_SIZE = 25;

    #[\Override]
    public function getLabel(): string
    {
        return 'oro.akeneo.connector.category.label';
    }

    #[\Override]
    public function getImportEntityFQCN(): string
    {
        return Category::class;
    }

    #[\Override]
    public function getImportJobName(): string
    {
        return self::IMPORT_JOB_NAME;
    }

    #[\Override]
    public function getType(): string
    {
        return 'category';
    }

    #[\Override]
    protected function getConnectorSource(): iterable
    {
        return $this->transport->getCategories(self::PAGE_SIZE);
    }
}
