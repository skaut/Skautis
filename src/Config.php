<?php

namespace Skautis;

/**
 * Trida pro uzivatelske nastaveni
 */
class Config
{

    const CACHE_ENABLED = true;
    const CACHE_DISABLED = false;

    const TESTMODE_ENABLED = true;
    const TESTMODE_DISABLED = false;

    const COMPRESSION_ENABLED = true;
    const COMPRESSION_DISABLED = false;

    const URL_TEST = "http://test-is.skaut.cz/";
    const URL_PRODUCTION = "https://is.skaut.cz/";

    /**
     * @var bool
     */
    protected $appId = '';

    /**
     * používat kompresi?
     * @var bool
     */
    protected $compression = true;

    /**
     * používat testovací Skautis?
     * @var bool
     */
    protected $testMode = false;

    /**
     * Cachovat WSDL
     * @var bool
     */
    protected $cache = true;


    /**
     * @param string $appId Id aplikace od spravce skautisu
     */
    public function __construct($appId, $testMode = false, $cache = true, $compression = true)
    {
        $this->appId = $appId;
        $this->testMode = $testMode;
        $this->cache = $cache;
        $this->compression = $compression;
    }

    /**
     * Kontrola koretnosti nastaveni.
     *
     * Kontroluje zda jsou nastavena vsechna nutna nastaveni. Kontroluje validitu nastaveni a korektnost typu.
     *
     * @return bool
     */
    public function validate()
    {
        $valid = true
           && !empty($this->appId)
           && is_bool($this->compression)
	   && is_bool($this->cache)
	   && is_bool($this->testMode);

        return $valid;
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
    public function isSetAppId()
    {
        return !empty($this->appId);
    }

    /**
     * @return bool
     */
    public function getCompression()
    {
        return $this->compression;
    }

    /**
     * @return void
     */
    public function setCompression($compression)
    {
        $this->compression = $compression;
        return $this;
    }

    /**
     * @return bool
     */
    public function getTestMode()
    {
        return $this->testMode;
    }


    /**
     * @return void
     */
    public function setTestMode($isTestMode)
    {
        $this->testMode = $isTestMode;
        return $this;
    }


    /**
     * Vypne cachovani WSDL
     *
     * @return void
     */
    public function setCache($cache)
    {
	$this->cache = $cache;
    }

    /**
     * Zjisti jestli je WSDL cachovane
     *
     * @return bool
     */
    public function getCache()
    {
	return $this->cache;
    }

    /**
     * vrací začátek URL adresy
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->testMode ? self::URL_TEST : self::URL_PRODUCTION;
    }

    /**
     * Na zaklade nastaveni vraci argumenty pro SoapClient
     *
     * @see \SoapClient
     *
     * @return array
     */
    public function getSoapArguments()
    {
        $soapOpts = [
            'ID_Application' => $this->appId,
            'soap_version' => SOAP_1_2,
            'encoding' => 'utf-8',
        ];

        if ($this->compression) {
            $soapOpts['compression'] = SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP;
        }

        $soapOpts['cache_wsdl'] = $this->cache ? WSDL_CACHE_BOTH : WSDL_CACHE_NONE;

        return $soapOpts;
    }

}
