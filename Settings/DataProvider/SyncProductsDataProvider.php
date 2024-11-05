<?php

namespace Creativestyle\Bundle\AkeneoBundle\Settings\DataProvider;

class SyncProductsDataProvider implements SyncProductsDataProviderInterface
{
    public const PUBLISHED = 'published';

    public const ALL_PRODUCTS = 'all_products';

    public function getSyncProducts(): array
    {
        return [
            self::ALL_PRODUCTS,
            self::PUBLISHED,
        ];
    }

    public function getDefaultValue(): string
    {
        return self::ALL_PRODUCTS;
    }
}
