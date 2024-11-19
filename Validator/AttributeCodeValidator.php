<?php

namespace Creativestyle\Bundle\AkeneoBundle\Validator;

use Creativestyle\Bundle\AkeneoBundle\Validator\Constraints\AttributeCodeConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AttributeCodeValidator extends ConstraintValidator
{
    #[\Override]
    public function validate($value, Constraint $constraint): void
    {
        /* @var AttributeCodeConstraint $constraint */
        if (!empty($value) && !$this->isAkeneoConform($value)) {
            $this->context->addViolation($constraint->message);
        }
    }

    private function isAkeneoConform(string $value): bool
    {
        preg_match_all('/([^a-zA-Z0-9_;])/m', $value, $matches, \PREG_SET_ORDER, 0);

        return count($matches) === 0;
    }
}
