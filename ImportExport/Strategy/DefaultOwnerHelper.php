<?php

namespace Creativestyle\Bundle\AkeneoBundle\ImportExport\Strategy;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;

class DefaultOwnerHelper
{
    public function __construct(
        private DoctrineHelper $doctrineHelper,
        private OwnershipMetadataProviderInterface $ownershipMetadataProvider
    ) {
    }

    public function populateChannelOwner($entity, Integration $integration): void
    {
        $defaultUserOwner = $integration->getDefaultUserOwner();

        $className         = $this->doctrineHelper->getEntityClass($entity);
        $doctrineMetadata  = $this->doctrineHelper->getEntityMetadata($className);
        $ownershipMetadata = $this->ownershipMetadataProvider->getMetadata($className);

        if ($doctrineMetadata && $defaultUserOwner && $ownershipMetadata->isUserOwned()) {
            $doctrineMetadata->setFieldValue(
                $entity,
                $ownershipMetadata->getOwnerFieldName(),
                $defaultUserOwner
            );
        }

        if ($defaultUserOwner && $ownershipMetadata->isBusinessUnitOwned()) {
            $doctrineMetadata->setFieldValue(
                $entity,
                $ownershipMetadata->getOwnerFieldName(),
                $defaultUserOwner->getOwner()
            );
        }

        $defaultOrganization = $integration->getOrganization();
        if ($defaultOrganization && $ownershipMetadata->getOrganizationFieldName()) {
            $doctrineMetadata->setFieldValue(
                $entity,
                $ownershipMetadata->getOrganizationFieldName(),
                $defaultOrganization
            );
        }
    }
}
