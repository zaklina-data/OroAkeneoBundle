<?php

namespace Creativestyle\Bundle\AkeneoBundle\ImportExport\Serializer\Normalizer;

use Creativestyle\Bundle\AkeneoBundle\Integration\AkeneoChannel;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;

class AkeneoNormalizerWrapper implements ContextAwareDenormalizerInterface
{
    public function __construct(private ContextAwareDenormalizerInterface $fileNormalizer)
    {
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        if ($this->fileNormalizer->supportsDenormalization($data, $type, $format, $context)) {
            return AkeneoChannel::TYPE === ($context['channelType'] ?? null);
        }

        return false;
    }

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        return $this->fileNormalizer->denormalize($data, $type, $format, $context);
    }
}
