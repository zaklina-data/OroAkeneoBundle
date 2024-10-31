<?php

namespace Creativestyle\Bundle\AkeneoBundle\Entity;

use Creativestyle\Bundle\AkeneoBundle\Entity\Repository\AkeneoSettingsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\PricingBundle\Entity\PriceList;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
#[ORM\Entity(repositoryClass: AkeneoSettingsRepository::class)]
class AkeneoSettings extends Transport
{
    public const TWO_LEVEL_FAMILY_VARIANT_FIRST_ONLY = 'first_only';
    public const TWO_LEVEL_FAMILY_VARIANT_SECOND_ONLY = 'second_only';
    public const TWO_LEVEL_FAMILY_VARIANT_BOTH = 'both';
    public const DEFAULT_ATTRIBUTES_MAPPING = 'name:names;description:descriptions;';
    public const DEFAULT_BRAND_MAPPING = 'label:names';

    #[ORM\Column(name: 'akeneo_sync_products', type: 'string', length: 255, nullable: false)]
    protected string $syncProducts = '';

    #[ORM\Column(name: 'akeneo_product_unit_attribute', type: 'string', length: 255, nullable: true)]
    protected ?string $productUnitAttribute = null;

    #[ORM\Column(name: 'akeneo_unit_precision_attr', type: 'string', length: 255, nullable: true)]
    protected ?string $productUnitPrecisionAttribute = null;

    #[ORM\Column(name: 'akeneo_channels', type: 'array', nullable: true)]
    protected ?array $akeneoChannels = null;

    #[ORM\Column(name: 'akeneo_active_channel', type: 'string', nullable: true)]
    protected ?string $akeneoActiveChannel = null;

    #[ORM\Column(name: 'akeneo_currencies', type: 'array', nullable: true)]
    protected ?array $akeneoCurrencies = null;

    #[ORM\Column(name: 'akeneo_active_currencies', type: 'array', nullable: true)]
    protected ?array $akeneoActiveCurrencies = null;

    #[ORM\Column(name: 'akeneo_locales_list', type: 'array', nullable: true)]
    protected ?array $akeneoLocalesList = null;

    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    protected ?Category $rootCategory = null;

    #[ORM\Column(name: 'akeneo_acl_voter_enabled', type: 'boolean')]
    protected bool $aclVoterEnabled = true;

    #[ORM\Column(name: 'akeneo_product_filter', type: 'text', nullable: true)]
    protected ?string $productFilter = null;

    #[ORM\Column(name: 'akeneo_conf_product_filter', type: 'text', nullable: true)]
    protected ?string $configurableProductFilter = null;

    #[ORM\Column(name: 'akeneo_url', type: 'string', length: 100)]
    private string $url = '';

    #[ORM\Column(name: 'akeneo_client_id', type: 'string', length: 100)]
    private string $clientId = '';

    #[ORM\Column(name: 'akeneo_secret', type: 'string', length: 100)]
    private string $secret = '';

    #[ORM\Column(name: 'akeneo_username', type: 'string', length: 200)]
    private string $username = '';

    #[ORM\Column(name: 'akeneo_password', type: 'string', length: 200)]
    private string $password = '';
    #[ORM\Column(name: 'akeneo_token', type: 'string', length: 200)]
    private string $token = '';

    #[ORM\Column(name: 'akeneo_refresh_token', type: 'string', length: 200)]
    private string $refreshToken = '';

    #[ORM\Column(name: 'akeneo_token_expiry_date_time', type: 'datetime', nullable: true)]
    private ?\DateTime $tokenExpiryDateTime = null;

    #[ORM\OneToMany(
        mappedBy: 'akeneoSettings',
        targetEntity: AkeneoLocale::class,
        cascade: ['persist'],
        fetch: 'EAGER',
        orphanRemoval: true)
    ]
    private Collection $akeneoLocales;

    #[ORM\ManyToOne(targetEntity: PriceList::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?PriceList $priceList = null;

    #[ORM\Column(name: 'akeneo_attributes_list', type: 'text', nullable: true)]
    private ?string $akeneoAttributesList = null;

    #[ORM\Column(name: 'akeneo_attributes_image_list', type: 'text', nullable: true)]
    private ?string $akeneoAttributesImageList;

    #[ORM\Column(name: 'akeneo_merge_image_to_parent', type: 'boolean', options: ['default' => false])]
    private bool $akeneoMergeImageToParent = false;

    #[ORM\Column(name: 'akeneo_variant_levels', type: 'string', length: 255)]
    private string $akeneoVariantLevels = '';

    #[ORM\Column(name: 'akeneo_attributes_mapping', type: 'text', nullable: true)]
    private ?string $akeneoAttributesMapping = null;

    #[ORM\Column(name: 'akeneo_brand_reference_code', type: 'string', length: 255)]
    private string $akeneoBrandReferenceEntityCode = '';

    #[ORM\Column(name: 'akeneo_brand_mapping', type: 'text', nullable: true)]
    private ?string $akeneoBrandMapping = null;

    private ?ParameterBag $settings = null;

    public function __construct()
    {
        $this->akeneoLocales = new ArrayCollection();
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function addAkeneoLocale(AkeneoLocale $akeneoLocale): static
    {
        $this->akeneoLocales[] = $akeneoLocale;
        $akeneoLocale->setAkeneoSettings($this);

        return $this;
    }

    public function removeAkeneoLocale(AkeneoLocale $akeneoLocale): static
    {
        $this->akeneoLocales->removeElement($akeneoLocale);
        $akeneoLocale->setAkeneoSettings(null);

        return $this;
    }

    public function getProductFilter(): string
    {
        return $this->productFilter;
    }

    public function setProductFilter(string $productFilter): static
    {
        $this->productFilter = $productFilter;

        return $this;
    }

    public function getConfigurableProductFilter(): ?string
    {
        return $this->configurableProductFilter;
    }

    public function setConfigurableProductFilter(string $configurableProductFilter): static
    {
        $this->configurableProductFilter = $configurableProductFilter;

        return $this;
    }

    public function getSettingsBag(): ParameterBag
    {
        if (null === $this->settings) {
            $this->settings = new ParameterBag(
                [
                    'clientId' => $this->getClientId(),
                    'secret' => $this->getSecret(),
                    'akeneoChannels' => $this->getAkeneoChannels(),
                    'akeneoActiveChannel' => $this->getAkeneoActiveChannel(),
                    'username' => $this->getUsername(),
                    'password' => $this->getPassword(),
                    'token' => $this->getToken(),
                    'refreshToken' => $this->getRefreshToken(),
                    'syncProducts' => $this->getSyncProducts(),
                    'productUnitAttribute' => $this->getProductUnitAttribute(),
                    'productUnitPrecisionAttribute' => $this->getProductUnitPrecisionAttribute(),
                    'akeneoCurrencies' => $this->getAkeneoCurrencies(),
                    'akeneoActiveCurrencies' => $this->getAkeneoActiveCurrencies(),
                    'akeneoLocales' => $this->getAkeneoLocales()->toArray(),
                    'akeneoLocalesList' => $this->getAkeneoLocalesList(),
                    'akeneoAttributesList' => $this->getAkeneoAttributesList(),
                    'akeneoVariantLevels' => $this->getAkeneoVariantLevels(),
                    'akeneoAttributesMapping' => $this->getAkeneoAttributesMapping(),
                    'akeneoBrandReferenceEntityCode' => $this->getAkeneoBrandReferenceEntityCode(),
                    'akeneoBrandMapping' => $this->getAkeneoBrandMapping(),
                ]
            );
        }

        return $this->settings;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function setClientId(string $clientId): static
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function setSecret(string $secret): static
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * Gets akeneoChannels.
     */
    public function getAkeneoChannels(): ?array
    {
        return $this->akeneoChannels;
    }

    /**
     * Sets akeneoChannels.
     */
    public function setAkeneoChannels(array $akeneoChannels = null): static
    {
        $this->akeneoChannels = $akeneoChannels;

        return $this;
    }

    /**
     * Gets akeneoActiveChannel.
     */
    public function getAkeneoActiveChannel(): ?string
    {
        return $this->akeneoActiveChannel;
    }

    /**
     * Sets akeneoActiveChannel.
     */
    public function setAkeneoActiveChannel(?string $akeneoActiveChannel = null): static
    {
        $this->akeneoActiveChannel = $akeneoActiveChannel;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername($username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $refreshToken): static
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * Get syncProducts.
     */
    public function getSyncProducts(): string
    {
        return $this->syncProducts;
    }

    /**
     * Set syncProducts.
     */
    public function setSyncProducts(string $syncProducts): static
    {
        $this->syncProducts = $syncProducts;

        return $this;
    }

    public function getProductUnitAttribute(): ?string
    {
        return $this->productUnitAttribute;
    }

    public function setProductUnitAttribute(string $productUnitAttribute): static
    {
        $this->productUnitAttribute = $productUnitAttribute;

        return $this;
    }

    public function getProductUnitPrecisionAttribute(): ?string
    {
        return $this->productUnitPrecisionAttribute;
    }

    public function setProductUnitPrecisionAttribute(string $productUnitPrecisionAttribute): static
    {
        $this->productUnitPrecisionAttribute = $productUnitPrecisionAttribute;

        return $this;
    }

    public function getAkeneoCurrencies(): ?array
    {
        return $this->akeneoCurrencies;
    }

    /**
     * Sets akeneoCurrencies.
     */
    public function setAkeneoCurrencies(array $akeneoCurrencies = null): static
    {
        $this->akeneoCurrencies = $akeneoCurrencies;

        return $this;
    }

    /**
     * Gets akeneoActiveCurrencies.
     */
    public function getAkeneoActiveCurrencies(): ?array
    {
        return $this->akeneoActiveCurrencies;
    }

    /**
     * Sets akeneoActiveCurrencies.
     */
    public function setAkeneoActiveCurrencies(array $akeneoActiveCurrencies = null): static
    {
        $this->akeneoActiveCurrencies = $akeneoActiveCurrencies;

        return $this;
    }

    /**
     * @return Collection|AkeneoLocale[]
     */
    public function getAkeneoLocales(): Collection
    {
        return $this->akeneoLocales;
    }

    /**
     * Gets akeneoLocalesList.
     */
    public function getAkeneoLocalesList(): ?array
    {
        return $this->akeneoLocalesList;
    }

    /**
     * Sets akeneoLocalesList.
     */
    public function setAkeneoLocalesList(array $akeneoLocalesList = null): static
    {
        $this->akeneoLocalesList = $akeneoLocalesList;

        return $this;
    }

    public function getAkeneoAttributesImageList(): ?string
    {
        return $this->akeneoAttributesImageList;
    }

    public function setAkeneoAttributesImageList(string $akeneoAttributesImageList = null): self
    {
        $this->akeneoAttributesImageList = $akeneoAttributesImageList;

        return $this;
    }

    public function getTokenExpiryDateTime(): ?\Datetime
    {
        return $this->tokenExpiryDateTime;
    }

    public function setTokenExpiryDateTime(\DateTime $tokenExpiryDateTime): self
    {
        $this->tokenExpiryDateTime = $tokenExpiryDateTime;

        return $this;
    }

    /**
     * Get root category.
     */
    public function getRootCategory(): ?Category
    {
        return $this->rootCategory;
    }

    /**
     * Set root category.
     */
    public function setRootCategory(Category $rootCategory = null): self
    {
        $this->rootCategory = $rootCategory;

        return $this;
    }

    /**
     * Get mapped locale.
     */
    public function getMappedAkeneoLocale(string $locale): ?string
    {
        foreach ($this->getAkeneoLocales() as $akeneoLocale) {
            if ($akeneoLocale->getLocale() === $locale) {
                return $akeneoLocale->getCode();
            }
        }

        return null;
    }

    public function isAclVoterEnabled(): bool
    {
        return $this->aclVoterEnabled;
    }

    public function setAclVoterEnabled(bool $aclVoterEnabled): self
    {
        $this->aclVoterEnabled = $aclVoterEnabled;

        return $this;
    }

    public function getPriceList(): ?PriceList
    {
        return $this->priceList;
    }

    public function setPriceList(PriceList $priceList): self
    {
        $this->priceList = $priceList;

        return $this;
    }

    public function getAkeneoAttributesList(): ?string
    {
        return $this->akeneoAttributesList;
    }

    public function setAkeneoAttributesList(string $attributeList = null): self
    {
        $this->akeneoAttributesList = $attributeList;

        return $this;
    }

    public function isAkeneoMergeImageToParent(): bool
    {
        return $this->akeneoMergeImageToParent;
    }

    public function setAkeneoMergeImageToParent(bool $akeneoMergeImageToParent): self
    {
        $this->akeneoMergeImageToParent = $akeneoMergeImageToParent;

        return $this;
    }

    public function getAkeneoVariantLevels(): ?string
    {
        return $this->akeneoVariantLevels;
    }

    public function setAkeneoVariantLevels(string $akeneoVariantLevels): self
    {
        $this->akeneoVariantLevels = $akeneoVariantLevels;

        return $this;
    }

    public function getAkeneoAttributesMapping(): ?string
    {
        return $this->akeneoAttributesMapping;
    }

    public function setAkeneoAttributesMapping(string $akeneoAttributesMapping): self
    {
        $this->akeneoAttributesMapping = $akeneoAttributesMapping;

        return $this;
    }

    public function getAkeneoBrandReferenceEntityCode(): ?string
    {
        return $this->akeneoBrandReferenceEntityCode;
    }

    public function setAkeneoBrandReferenceEntityCode(?string $akeneoBrandReferenceEntityCode): void
    {
        $this->akeneoBrandReferenceEntityCode = $akeneoBrandReferenceEntityCode;
    }

    public function getAkeneoBrandMapping(): ?string
    {
        return $this->akeneoBrandMapping;
    }

    public function setAkeneoBrandMapping(?string $akeneoBrandMapping): void
    {
        $this->akeneoBrandMapping = $akeneoBrandMapping;
    }
}
