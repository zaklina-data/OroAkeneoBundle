<?php

namespace Creativestyle\Bundle\AkeneoBundle\Integration\Iterator;

use Akeneo\Pim\ApiClient\AkeneoPimClientInterface;
use Akeneo\Pim\ApiClient\Pagination\ResourceCursorInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

abstract class AbstractIterator implements \Iterator
{
    use LoggerAwareTrait;

    protected const PAGE_SIZE = 100;

    /**
     * AttributeIterator constructor.
     */
    public function __construct(
        protected ResourceCursorInterface $resourceCursor,
        protected AkeneoPimClientInterface $client,
        LoggerInterface $logger
    ) {
        $this->setLogger($logger);
    }

    #[\Override]
    final public function current()
    {
        return $this->doCurrent();
    }

    /**
     * current() login.
     */
    abstract public function doCurrent();

    #[\Override]
    public function next()
    {
        $this->resourceCursor->next();
    }

    #[\Override]
    public function key()
    {
        return $this->resourceCursor->key();
    }

    #[\Override]
    public function valid()
    {
        return $this->resourceCursor->valid();
    }

    #[\Override]
    public function rewind()
    {
        $this->resourceCursor->rewind();
    }
}
