<?php

namespace Creativestyle\Bundle\AkeneoBundle\ProductVariant\TypeHandler;

use Oro\Bundle\ProductBundle\ProductVariant\Registry\ProductVariantTypeHandlerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;

class StringTypeHandler implements ProductVariantTypeHandlerInterface
{
    private const TYPE = 'string';

    public function __construct(protected FormFactory $formFactory)
    {
    }

    public function createForm($fieldName, array $availability, array $options = []): FormInterface
    {
        $options = array_merge($this->getOptions($fieldName, $availability), $options);

        return $this->formFactory->createNamed($fieldName, ChoiceType::class, null, $options);
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    private function getOptions(string $fieldName, array $availability): array
    {
        $values = array_keys($availability);

        return [
            'auto_initialize' => false,
            'choices' => array_combine($values, $values),
        ];
    }
}
