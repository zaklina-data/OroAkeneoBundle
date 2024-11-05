<?php

namespace Creativestyle\Bundle\AkeneoBundle\Validator;

use Creativestyle\Bundle\AkeneoBundle\Validator\Constraints\JsonConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class JsonValidator extends ConstraintValidator
{
    #[\Override]
    public function validate($value, Constraint $constraint): void
    {
        /* @var JsonConstraint $constraint */
        if (!$this->isJSON($value) && strlen($value) > 0) {
            $this->context->addViolation($constraint->message);
        }
    }

    private function isJSON(mixed $string): bool
    {
        return is_string($string)
            && is_array(json_decode($string, true, 512, JSON_THROW_ON_ERROR))
            && \JSON_ERROR_NONE === json_last_error();
    }
}
