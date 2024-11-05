<?php

namespace Creativestyle\Bundle\AkeneoBundle\Validator;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Validator\Constraints\ConfigurableProductAccessorTrait;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UniqueVariantLinksSimpleProductValidator extends ConstraintValidator
{
    use ConfigurableProductAccessorTrait;

    public function __construct(
        private DoctrineHelper $doctrineHelper,
        private ConstraintValidatorInterface $validator
    ) {
    }

    public function initialize(ExecutionContextInterface $context): void
    {
        $this->validator->initialize($context);

        parent::initialize($context);
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!is_a($value, Product::class)) {
            $message = sprintf(
                'Entity must be instance of "%s", "%s" given',
                Product::class,
                is_object($value) ? get_class($value) : gettype($value)
            );
            throw new \InvalidArgumentException($message);
        }

        if ($value->isConfigurable() || $value->getParentVariantLinks()->count() === 0) {
            return;
        }

        $uow = $this->doctrineHelper->getEntityManagerForClass(Product::class)->getUnitOfWork();
        $collections = array_merge($uow->getScheduledCollectionUpdates(), $uow->getScheduledCollectionDeletions());
        if (empty($uow->getEntityChangeSet($value)['variantFields'])
            && !in_array($value->getVariantLinks(), $collections, true)
            && !in_array($value->getParentVariantLinks(), $collections, true)
        ) {
            return;
        }

        $this->validator->validate($value, $constraint);
    }
}
