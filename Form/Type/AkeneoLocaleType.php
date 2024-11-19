<?php

namespace Creativestyle\Bundle\AkeneoBundle\Form\Type;

use Creativestyle\Bundle\AkeneoBundle\Entity\AkeneoLocale;
use Oro\Bundle\FormBundle\Form\Type\OroChoiceType;
use Oro\Bundle\LocaleBundle\Manager\LocalizationManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Intl\Locales;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\InvalidOptionsException;
use Symfony\Component\Validator\Exception\MissingOptionsException;

class AkeneoLocaleType extends AbstractType implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const BLOCK_PREFIX = 'oro_akeneo_locale';

    public array $codes = [];

    public function __construct(private LocalizationManager $localizationManager)
    {
    }

    /**
     * @throws ConstraintDefinitionException
     * @throws InvalidOptionsException
     * @throws MissingOptionsException
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->codes = $options['parent_data'] ?? [];

        $builder
            ->add(
                'code',
                ChoiceType::class,
                [
                    'choices' => array_combine($this->codes, $this->codes),
                    'choice_label' => function ($choice) {
                        return Locales::getName($choice);
                    },
                    'label' => false,
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]
            )
            ->add(
                'locale',
                OroChoiceType::class,
                [
                    'required' => true,
                    'label' => false,
                    'constraints' => [
                        new NotBlank(),
                    ],
                    'choices' => $this->getChoices(),
                ]
            );
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
    }

    private function getChoices(): array
    {
        $choices = [];
        $localizations = $this->localizationManager->getLocalizations();
        foreach ($localizations as $localization) {
            $choices[$localization->getName()] = $localization->getLanguageCode();
        }

        return $choices;
    }

    public function onPreSetData(FormEvent $event): void
    {
        $form = $event->getForm();

        /** @var AkeneoLocale $data */
        $data = $event->getData();

        if (!$data instanceof AkeneoLocale) {
            return;
        }

        $form
            ->add(
                'code',
                ChoiceType::class,
                [
                    'choices' => array_combine($this->codes, $this->codes),
                    'choice_label' => function ($choice) {
                        return Locales::getName($choice);
                    },
                ]
            );
    }

    public function onPreSubmit(FormEvent $event): void
    {
        try {
            $transportData = $event->getData();
            $form = $event->getForm();

            $form
                ->add(
                    'code',
                    ChoiceType::class,
                    [
                        'choices' => array_combine($this->codes, $this->codes),
                        'choice_label' => function ($choice) {
                            return Locales::getName($choice);
                        },
                    ]
                );

            $event->setData($transportData);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    /**
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => AkeneoLocale::class,
                'parent_data' => [],
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return self::BLOCK_PREFIX;
    }
}
