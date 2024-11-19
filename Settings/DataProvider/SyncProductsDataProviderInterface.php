<?php

namespace Creativestyle\Bundle\AkeneoBundle\Settings\DataProvider;

/**
 * @deprecated It will always sync all products.
 */
interface SyncProductsDataProviderInterface
{
    /**
     * @return string[]
     */
    public function getSyncProducts(): array;

    public function getDefaultValue(): string;
}
