<?php

namespace Creativestyle\Bundle\AkeneoBundle\Placeholder;

use Creativestyle\Bundle\AkeneoBundle\Integration\AkeneoChannel;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Helper\EntityConfigProviderHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

/**
 * Checks if schema update should be applicable.
 */
class SchemaUpdateFilter
{
    private const ACTION_NAME = 'oro.entity_extend.entity_config.extend.field.layout_action.update_schema';

    /**
     * SchemaUpdateFilter constructor.
     */
    public function __construct(
        private ConfigManager $configManager,
        private EntityConfigProviderHelper $entityConfigProviderHelper
    ) {
    }

    /**
     * Check if schema update button is applicable.
     *
     * @param object $entity
     */
    public function isApplicable($entity, string $entityConfigModelClass): bool
    {
        if (false === is_a($entity, Channel::class) || AkeneoChannel::TYPE !== $entity->getType()) {
            return false;
        }

        $entityConfigModel = $this->configManager->getConfigEntityModel($entityConfigModelClass);
        list($actions) = $this->entityConfigProviderHelper->getLayoutParams($entityConfigModel);

        return $this->containsSchemaUpdateAction($actions);
    }

    /**
     * Check if actions array contains schema update.
     */
    private function containsSchemaUpdateAction(array $actions): bool
    {
        foreach ($actions as $action) {
            if (self::ACTION_NAME === $action['name']) {
                return true;
            }
        }

        return false;
    }
}
