<?php

namespace Creativestyle\Bundle\AkeneoBundle\ImportExport\Processor;

use Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface;

class AsyncProcessor implements ProcessorInterface
{
    #[\Override]
    public function process(mixed $item): mixed
    {
        return $item;
    }
}
