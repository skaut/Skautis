<?php
declare(strict_types = 1);

namespace Skautis;

/**
 * Třída pro uživatelské nastavení
 */
class Config
{

    public const CACHE_ENABLED = true;
    public const CACHE_DISABLED = false;

    public const TESTMODE_ENABLED = true;
    public const TESTMODE_DISABLED = false;

    public const COMPRESSION_ENABLED = true;
    public const COMPRESSION_DISABLED = false;

    private const URL_TEST = 'https://test-is.skaut.cz/';
    private const URL_PRODUCTION = 'https://is.skaut.cz/';

    /**
     * @var string
     */
    private $appId;

    /**
     * Používat testovací SkautIS?
     *
     * @var bool
     */
    private $testMode;

    /**
     * Používat kompresi?
     *
     * @var bool
     */
    private $compression;

    /**
     * Cachovat WSDL?
     *
     * @var bool
     */
    protected $cache;


    /**
     * @param string $appId Id aplikace od správce skautISu
     * @param bool $isTestMode používat testovací SkautIS?
     * @param bool $cache použít kompresi?
     * @param bool $compression cachovat WDSL?
     * @throws InvalidArgumentException
     */
    public function __construct(
      string $appId,
      bool $isTestMode = self::TESTMODE_DISABLED,
      bool $cache = self::CACHE_ENABLED,
      bool $compression = self::COMPRESSION_ENABLED
    ) {
        if (empty($appId)) {
            throw new InvalidArgumentException('AppId cannot be empty.');
        }
        $this->appId = $appId;
        $this->setTestMode($isTestMode);
        $this->setCache($cache);
        $this->setCompression($compression);
    }

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function isTestMode(): bool
    {
        return $this->testMode;
    }

    public function setTestMode(bool $isTestMode = true): self
    {
        $this->testMode = $isTestMode;
        return $this;
    }

    /**
     * Zjistí, jestli je WSDL cachované
     */
    public function getCache(): bool
    {
        return $this->cache;
    }

    /**
     * Vypne/zapne cachovaní WSDL
     */
    public function setCache(bool $enabled): self
    {
        $this->cache = $enabled;
        return $this;
    }

    /**
     * Zjistí, jestli se používá komprese dotazů na WSDL
     */
    public function getCompression(): bool
    {
        return $this->compression;
    }

    /**+
     * Vypne/zapne kompresi dotazů na WSDL
     */
    public function setCompression(bool $enabled): self
    {
        $this->compression = $enabled;
        return $this;
    }

    /**
     * Vací začátek URL adresy
     */
    public function getBaseUrl(): string
    {
        return $this->testMode ? self::URL_TEST : self::URL_PRODUCTION;
    }

    /**
     * Na základě nastavení vrací argumenty pro SoapClient
     *
     * @see \SoapClient
     */
    public function getSoapOptions(): array
    {
        $soapOptions = [
            'ID_Application' => $this->appId,
            'soap_version' => SOAP_1_2,
            'encoding' => 'utf-8',
        ];

        if ($this->compression) {
            $soapOptions['compression'] = SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP;
        }

        $soapOptions['cache_wsdl'] = $this->cache ? WSDL_CACHE_BOTH : WSDL_CACHE_NONE;

        return $soapOptions;
    }
}
