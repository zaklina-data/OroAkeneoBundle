<?php

namespace Creativestyle\Bundle\AkeneoBundle\ImportExport\Processor;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CacheBundle\Provider\MemoryCacheProviderAwareInterface;
use Oro\Bundle\CacheBundle\Provider\MemoryCacheProviderAwareTrait;
use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface;

class CategoryRemoveProcessor implements ProcessorInterface, MemoryCacheProviderAwareInterface
{
    use MemoryCacheProviderAwareTrait;

    public function __construct(private ManagerRegistry $registry)
    {
    }

    public function process(mixed $item): ?Category
    {
        if (!$item instanceof Category) {
            return null;
        }

        $id = $item->getId();
        $this->memoryCacheProvider->get(
            'category_id_' . $item->getAkeneoCode(),
            function () use ($id) {
                return $id;
            }
        );

        if ($this->memoryCacheProvider->get('category_' . $item->getAkeneoCode())) {
            return null;
        }

        return $item;
    }
}
