<?php

namespace Creativestyle\Bundle\AkeneoBundle\Entity;

use Creativestyle\Bundle\AkeneoBundle\Entity\Repository\AkeneoLocaleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AkeneoLocaleRepository::class)]
#[ORM\Table(name: 'oro_akeneo_locale')]
class AkeneoLocale
{
    #[ORM\Column(name: 'locale', type: 'string', length: 10, nullable: true)]
    protected ?string $locale = null;

    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id;

    #[ORM\Column(name: 'code', type: 'string', length: 200)]
    private string $code = '';

    #[ORM\ManyToOne(targetEntity: AkeneoSettings::class, inversedBy: 'akeneoLocales')]
    #[ORM\JoinColumn(referencedColumnName: 'id')]
    private ?AkeneoSettings $akeneoSettings = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale = null): self
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get $akeneoSettings.
     */
    public function getAkeneoSettings(): AkeneoSettings
    {
        return $this->akeneoSettings;
    }

    public function setAkeneoSettings(AkeneoSettings $akeneoSettings = null): self
    {
        $this->akeneoSettings = $akeneoSettings;

        return $this;
    }
}
