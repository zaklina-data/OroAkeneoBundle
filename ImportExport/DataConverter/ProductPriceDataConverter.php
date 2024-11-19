<?php

namespace Creativestyle\Bundle\AkeneoBundle\ImportExport\DataConverter;

use Creativestyle\Bundle\AkeneoBundle\ImportExport\AkeneoIntegrationTrait;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\PricingBundle\Entity\PriceList;
use Oro\Bundle\PricingBundle\ImportExport\DataConverter\ProductPriceDataConverter as BaseProductPriceDataConverter;

class ProductPriceDataConverter extends BaseProductPriceDataConverter
{
    use AkeneoIntegrationTrait;

    protected DoctrineHelper $doctrineHelper;

    public function setDoctrineHelper(DoctrineHelper $doctrineHelper): void
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    #[\Override]
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true): array
    {
        $importedRecord['quantity'] = 1;
        $importedRecord['unit'] = ['code' => $this->configManager->get('oro_product.default_unit')];
        $importedRecord['price_list_id'] = $this->getPriceListId();

        return parent::convertToImportFormat($importedRecord, $skipNullValues);
    }

    private function getPriceListId(): int
    {
        $transport = $this->getTransport();

        if ($transport && !$transport->getPriceList()) {
            return $this->getDefaultPriceListId();
        }

        return $transport->getPriceList()->getId();
    }

    #[\Override]
    protected function getHeaderConversionRules(): array
    {
        return [
            'sku' => 'product:sku',
            'amount' => 'value',
            'currency' => 'currency',
            'price_list_id' => 'priceList:id',
        ];
    }

    public function getDefaultPriceList(): ?PriceList
    {
        $defaultPriceListId = $this->getDefaultPriceListId();
        if (!$defaultPriceListId) {
            return null;
        }

        return $this->doctrineHelper
            ->getEntityManagerForClass(PriceList::class)
            ->getRepository(PriceList::class)
            ->find($defaultPriceListId);
    }

    public function getDefaultPriceListId(): ?int
    {
        return $this->configManager->get('oro_pricing.default_price_list');
    }
}
