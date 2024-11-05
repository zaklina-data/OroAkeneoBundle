<?php

namespace Creativestyle\Bundle\AkeneoBundle\ImportExport\DataConverter;

use Creativestyle\Bundle\AkeneoBundle\ImportExport\AkeneoIntegrationTrait;
use Creativestyle\Bundle\AkeneoBundle\Tools\Generator;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\ImportExportBundle\Context\ContextAwareInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\LocaleBundle\ImportExport\DataConverter\LocalizedFallbackValueAwareDataConverter;

class CategoryDataConverter extends LocalizedFallbackValueAwareDataConverter implements ContextAwareInterface
{
    use AkeneoIntegrationTrait;
    use LocalizationAwareTrait;

    protected DoctrineHelper $doctrineHelper;

    protected ContextInterface $context;

    public function setImportExportContext(ContextInterface $context): void
    {
        $this->context = $context;
    }

    public function setDoctrineHelper(DoctrineHelper $doctrineHelper): void
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    #[\Override]
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true): array
    {
        $this->setTitles($importedRecord);
        $this->setRootCategory($importedRecord);

        $importedRecord['channel:id'] = $this->context->getOption('channel');

        return parent::convertToImportFormat($importedRecord, $skipNullValues);
    }

    /**
     * Set titles with locales mapping from settings.
     */
    private function setTitles(array &$importedRecord): void
    {
        $defaultLocalization = $this->getDefaultLocalization();
        $defaultLocale = $this->getTransport()->getMappedAkeneoLocale($defaultLocalization->getLanguageCode());

        $importedRecord['titles'] = [
            'default' => [
                'fallback' => null,
                'string' => $importedRecord['labels'][$defaultLocale] ??
                    Generator::generateLabel($importedRecord['code']),
            ],
        ];

        foreach ($this->getTransport()->getAkeneoLocales() as $akeneoLocale) {
            foreach ($this->getLocalizations($akeneoLocale->getLocale()) as $localization) {
                if (!$localization || $defaultLocalization->getLanguageCode() === $localization->getLanguageCode()) {
                    continue;
                }

                $value = $importedRecord['labels'][$akeneoLocale->getCode()] ?? null;
                $importedRecord['titles'][$localization->getName()] = ['fallback' => null, 'string' => $value];
            }
        }
    }

    /**
     * Check root category setting from akeneo settings.
     */
    private function setRootCategory(array &$importedRecord)
    {
        if ($importedRecord['parent']) {
            $importedRecord['parentCategory:channel:id'] = $this->context->getOption('channel');
            $importedRecord['parentCategory:akeneo_code'] = $importedRecord['parent'];

            return;
        }

        $rootCategoryId = $this->getTransport()?->getRootCategory()?->getId();
        if (!$rootCategoryId) {
            return;
        }

        $importedRecord['parentCategory:id'] = $rootCategoryId;
    }

    #[\Override]
    protected function getHeaderConversionRules(): array
    {
        return [
            'titles' => 'titles',
            'code' => 'akeneo_code',
        ];
    }

    #[\Override]
    protected function getBackendHeader(): array
    {
        throw new \Exception('Normalization is not implemented!');
    }
}
