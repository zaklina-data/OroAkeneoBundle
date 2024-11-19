<?php

namespace Creativestyle\Bundle\AkeneoBundle\Integration\Connector;

use Creativestyle\Bundle\AkeneoBundle\Integration\AkeneoTransportInterface;
use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;
use Oro\Bundle\ProductBundle\Entity\Brand;

/**
 * Brand connector for integration sync.
 *
 * @property AkeneoTransportInterface $transport
 */
class BrandConnector extends AbstractConnector
{
    public function getLabel(): string
    {
        return 'oro.akeneo.connector.brand.label';
    }

    public function getImportEntityFQCN(): string
    {
        return Brand::class;
    }

    public function getImportJobName(): string
    {
        return 'akeneo_brand_import';
    }

    public function getType(): string
    {
        return 'brand';
    }

    protected function getConnectorSource(): iterable
    {
        return $this->transport->getBrands();
    }
}
