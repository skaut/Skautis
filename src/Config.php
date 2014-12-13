<?php

namespace Skautis;

/**
 * Trida pro uzivatelske nastaveni
 */
class Config
{

    const CACHE_ENABLED = true;
    const CACHE_DISABLED = false;

    const PROFILER_ENABLED = true;
    const PROFILER_DISABLED = false;

    const TESTMODE_ENABLED = true;
    const TESTMODE_DISABELD = false;

    const COMPRESSION_ENABLED = true;
    const COMPRESSION_DISABLED = false;

    const HTTP_PREFIX_TEST = "http://test-is";
    const HTTP_PREFIX = "https://is";

    /**
     * @var bool
     */
    protected $appId = '';

    /**
     * používat kompresi?
     * @var bool
     */
    protected $compression = false;

    /**
     * používat testovací Skautis?
     * @var bool
     */
    protected $testMode = false;

    /**
     * Cachovat WSDL
     * @var bool
     */
    protected $cache = false;

    /**
     *
     * @var bool
     */
    public $profiler = false;

    /**
     * @param string $appId Id aplikace od spravce skautisu
     */
    public function __construct($appId)
    {
	$this->appId = $appId;
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
	   && is_bool($this->testMode)
           && is_bool($this->profiler);

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
     * Zjisti jesli je zapnuto profilovani
     *
     * @return bool
     */
    public function getProfiler()
    {
	return $this->profiler;
    }

    /**
     * Nastavi profilovani
     *
     * @param bool $profiler
     *
     * @return void
     */
    public function setProfiler($profiler)
    {
	$this->profiler = $profiler;
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
    public function getHttpPrefix()
    {
        return $this->isTestMode ? self::HTTP_PREFIX_TEST : self::HTTP_PREFIX;
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


        if ($this->compression === true) {
		$soapOpts['compression'] = SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP;
	}


        if ($this->cache === true) {
            $soapOpts['compression'] = SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP;
	}

	return $soapOpts;
    }
}
