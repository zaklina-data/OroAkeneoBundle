<?php

namespace Creativestyle\Bundle\AkeneoBundle\ImportExport\Serializer\Normalizer;

use Creativestyle\Bundle\AkeneoBundle\Integration\AkeneoChannel;
use Oro\Bundle\AttachmentBundle\Entity\FileItem;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class FileItemNormalizer implements ContextAwareDenormalizerInterface
{
    public function __construct(private DenormalizerInterface $fileNormalizer)
    {
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return is_a($type, FileItem::class, true)
            && isset($context['channelType'])
            && AkeneoChannel::TYPE === $context['channelType'];
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = [])
    {
        $fileItem = new FileItem();
        $file = $this->fileNormalizer->denormalize($data, $type, $format, $context);
        $fileItem->setFile($file);

        return $fileItem;
    }
}
