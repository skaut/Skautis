<?php

namespace Skautis;

/**
 * Třída pro uživatelské nastavení
 */
class Config
{

    const CACHE_ENABLED = true;
    const CACHE_DISABLED = false;

    const TESTMODE_ENABLED = true;
    const TESTMODE_DISABLED = false;

    const COMPRESSION_ENABLED = true;
    const COMPRESSION_DISABLED = false;

    const URL_TEST = "https://test-is.skaut.cz/";
    const URL_PRODUCTION = "https://is.skaut.cz/";

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
    public function __construct($appId, $isTestMode = false, $cache = true, $compression = true)
    {
        if (empty($appId)) {
            throw new InvalidArgumentException("AppId cannot be empty.");
        }
        $this->appId = $appId;
        $this->setTestMode($isTestMode);
        $this->setCache($cache);
        $this->setCompression($compression);
    }

    /**
     * @return string
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @return bool
     */
    public function isTestMode()
    {
        return $this->testMode;
    }

    /**
     * @param bool $isTestMode
     * @return self
     */
    public function setTestMode($isTestMode = true)
    {
        $this->testMode = (bool) $isTestMode;
        return $this;
    }

    /**
     * Zjistí, jestli je WSDL cachované
     *
     * @return bool
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Vypne/zapne cachovaní WSDL
     *
     * @param bool $enabled
     * @return self
     */
    public function setCache($enabled)
    {
        $this->cache = (bool) $enabled;
        return $this;
    }

    /**
     * Zjistí, jestli se používá komprese dotazů na WSDL
     *
     * @return bool
     */
    public function getCompression()
    {
        return $this->compression;
    }

    /**
     * Vypne/zapne kompresi dotazů na WSDL
     *
     * @param $enabled
     * @return self
     */
    public function setCompression($enabled)
    {
        $this->compression = (bool) $enabled;
        return $this;
    }

    /**
     * Vací začátek URL adresy
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->testMode ? self::URL_TEST : self::URL_PRODUCTION;
    }

    /**
     * Na základě nastavení vrací argumenty pro SoapClient
     *
     * @see \SoapClient
     *
     * @return array
     */
    public function getSoapOptions()
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
