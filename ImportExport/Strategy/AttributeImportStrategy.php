<?php

namespace Creativestyle\Bundle\AkeneoBundle\ImportExport\Strategy;

use Oro\Bundle\EntityBundle\Helper\FieldHelper;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Entity\FieldConfigModel;
use Oro\Bundle\EntityConfigBundle\ImportExport\Strategy\EntityFieldImportStrategy;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;

/**
 * Strategy to import attributes.
 */
class AttributeImportStrategy extends EntityFieldImportStrategy
{
    use StrategyValidationTrait;

    protected ConfigManager $configManager;

    public function setFieldHelper(FieldHelper $fieldHelper): void
    {
        $this->fieldHelper = $fieldHelper;
    }

    public function setConfigManager(ConfigManager $configManager): void
    {
        $this->configManager = $configManager;
    }

    /**
     * @param FieldConfigModel $entity
     */
    protected function beforeProcessEntity($entity): ?object
    {
        if (!$entity->getType()) {
            return null;
        }

        $extendProvider = $this->configManager->getProvider('extend');
        if ($entity->getId()) {
            $extendConfig = $extendProvider->getConfig($entity->getEntity()->getClassName(), $entity->getFieldName());

            if (ExtendScope::OWNER_SYSTEM === $extendConfig->get('owner')) {
                return null;
            }
        }

        if ($extendProvider->hasConfig($entity->getEntity()->getClassName(), $entity->getFieldName())) {
            $extendConfig = $extendProvider->getConfig($entity->getEntity()->getClassName(), $entity->getFieldName());
            if ($extendConfig->in('state', [ExtendScope::STATE_DELETE, ExtendScope::STATE_RESTORE])) {
                return null;
            }
        }

        return parent::beforeProcessEntity($entity);
    }

    #[\Override]
    protected function processEntity(FieldConfigModel $entity): ?FieldConfigModel
    {
        $supportedTypes = $this->fieldTypeProvider->getSupportedFieldTypes();
        $relationTypes = $this->fieldTypeProvider->getSupportedRelationTypes();

        if (!in_array($entity->getType(), $supportedTypes, true)
            && !in_array($entity->getType(), $relationTypes, true)
        ) {
            $this->addErrors($this->translator->trans('oro.entity_config.import.message.invalid_field_type'));

            return null;
        }

        return $entity;
    }

    protected function addErrors($errors): void
    {
        $this->context->incrementErrorEntriesCount();
        foreach ((array)$errors as $validationError) {
            $this->context->addError(
                $this->translator->trans(
                    'oro.akeneo.error',
                    [
                        '%error%' => $validationError,
                        '%item%' => json_encode(
                            $this->context->getValue('rawItemData'),
                            JSON_THROW_ON_ERROR|\JSON_UNESCAPED_SLASHES|\JSON_UNESCAPED_UNICODE
                        ),
                    ]
                )
            );
        }
    }
}
