<?php

namespace Creativestyle\Bundle\AkeneoBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Oro\Bundle\EntityConfigBundle\Attribute\Entity\AttributeGroupRelation;
use Oro\Bundle\EntityConfigBundle\EventListener\DeletedAttributeRelationListener as BaseListener;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;

class DeletedAttributeRelationListener extends BaseListener
{
    protected array $deletedAttributesNames = [];

    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $uow = $eventArgs->getObjectManager()->getUnitOfWork();

        foreach ($uow->getScheduledEntityDeletions() as $attributeRelation) {
            if (!$attributeRelation instanceof AttributeGroupRelation) {
                continue;
            }
            $attributeFamily = $attributeRelation->getAttributeGroup()?->getAttributeFamily();

            if ($attributeFamily
                && $this->checkIsDeleted($attributeFamily, $attributeRelation->getEntityConfigFieldId())
            ) {
                $this->deletedAttributes[$attributeFamily->getId()][] = $attributeRelation->getEntityConfigFieldId();
            }
        }

        foreach ($this->deletedAttributes as $attributeFamilyId => $attributeIds) {
            $attributes = $this->deletedAttributeProvider->getAttributesByIds($attributeIds);
            foreach ($attributes as &$attribute) {
                $attribute = $this->getAttributeName($attribute);
            }
            unset($attribute);

            $this->deletedAttributesNames[$attributeFamilyId] = array_merge(
                $this->deletedAttributesNames[$attributeFamilyId] ?? [],
                $attributes
            );
            unset($this->deletedAttributes[$attributeFamilyId]);
        }
    }

    public function postFlush(): void
    {
        foreach ($this->deletedAttributesNames as $attributeFamilyId => $attributeNames) {
            if (!$attributeNames) {
                continue;
            }

            $this->messageProducer->send(
                $this->topic,
                new Message(
                    ['attributeFamilyId' => $attributeFamilyId, 'attributeNames' => $attributeNames],
                    MessagePriority::NORMAL
                )
            );
        }

        $this->deletedAttributesNames = [];
    }
}
