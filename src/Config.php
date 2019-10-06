<?php
declare(strict_types = 1);

namespace Skautis;

/**
 * Třída pro uživatelské nastavení
 */
final class Config
{

    public const CACHE_ENABLED = true;
    public const CACHE_DISABLED = false;

    public const TEST_MODE_ENABLED = true;
    public const TEST_MODE_DISABLED = false;

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
      bool $isTestMode = self::TEST_MODE_ENABLED,
      bool $cache = self::CACHE_ENABLED,
      bool $compression = self::COMPRESSION_ENABLED
    ) {
        if (empty($appId)) {
            throw new InvalidArgumentException('AppId cannot be empty.');
        }
        $this->appId = $appId;
        $this->testMode = $isTestMode;
        $this->cache = $cache;
        $this->compression = $compression;
    }

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function isTestMode(): bool
    {
        return $this->testMode;
    }

    /**
     * Zjistí, jestli je WSDL cachované
     */
    public function isCacheEnabled(): bool
    {
        return $this->cache;
    }

    /**
     * Zjistí, jestli se používá komprese dotazů na WSDL
     */
    public function isCompressionEnabled(): bool
    {
        return $this->compression;
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
     * Neumožňujeme uživateli primo modifikovat options aby byly vzdy validni a kompatibilni se Skautis API
     *
     * @see \SoapClient
     */
    public function getSoapOptions(): array
    {
        $soapOptions = [
            'ID_Application' => $this->appId,
            'soap_version' => SOAP_1_2,
            'encoding' => 'utf-8',
            'ssl_method' => SOAP_SSL_METHOD_TLS,
            'exceptions' => true,
            'trace' => true,
            'user_agent' => 'Skautis PHP library',
            'keep_alive' => true
        ];

        if ($this->compression) {
            $soapOptions['compression'] = SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP;
        }

        $soapOptions['cache_wsdl'] = $this->cache ? WSDL_CACHE_BOTH : WSDL_CACHE_NONE;

        return $soapOptions;
    }
}
