<?php

namespace Creativestyle\Bundle\AkeneoBundle\ImportExport\Processor;

use Creativestyle\Bundle\AkeneoBundle\Tools\AttributeFamilyCodeGenerator;
use Oro\Bundle\CacheBundle\Provider\MemoryCacheProviderAwareInterface;
use Oro\Bundle\CacheBundle\Provider\MemoryCacheProviderAwareTrait;
use Oro\Bundle\IntegrationBundle\ImportExport\Processor\StepExecutionAwareImportProcessor;

class AttributeFamilyImportProcessor extends StepExecutionAwareImportProcessor implements
    MemoryCacheProviderAwareInterface
{
    use MemoryCacheProviderAwareTrait;

    #[\Override]
    public function process($item)
    {
        if (!empty($item['code'])) {
            $code = AttributeFamilyCodeGenerator::generate($item['code']);
            $this->memoryCacheProvider->get(
                'attribute_family_' . $code,
                function () use ($code) {
                    return $code;
                }
            );
        }

        return parent::process($item);
    }
}
