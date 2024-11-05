<?php

namespace Creativestyle\Bundle\AkeneoBundle\ImportExport\Serializer\Normalizer;

use Creativestyle\Bundle\AkeneoBundle\Integration\AkeneoChannel;
use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\EntityConfigBundle\ImportExport\Serializer\EntityFieldNormalizer as BaseEntityFieldNormalizer;

class EntityFieldNormalizer extends BaseEntityFieldNormalizer
{
    #[\Override]
    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return is_array($data)
            && is_a($type, FieldConfigModel::class, true)
            && true === isset($context['channelType'])
            && AkeneoChannel::TYPE === $context['channelType'];
    }

    protected function getEnumConfig(): array
    {
        return [
            'id' => [
                self::CONFIG_TYPE => self::TYPE_STRING,
            ],
            'label' => [
                self::CONFIG_TYPE => self::TYPE_STRING,
            ],
            'is_default' => [
                self::CONFIG_TYPE => self::TYPE_BOOLEAN,
            ],
        ];
    }
}
