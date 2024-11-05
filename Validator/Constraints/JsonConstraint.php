<?php

namespace Creativestyle\Bundle\AkeneoBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class JsonConstraint extends Constraint
{
    public string $message = 'oro.akeneo.validator.ajax.message';

    #[\Override]
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }

    #[\Override]
    public function validatedBy(): string
    {
        return 'oro_akeneo.json_validator';
    }
}
