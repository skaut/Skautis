<?php

namespace Skautis;

class Config
{

    const HTTP_PREFIX_TEST = "http://test-is";
    const HTTP_PREFIX = "https://is";

    /**
     * používat kompresi?
     * @var bool
     */
    private $compression = TRUE;

    /**
     * používat testovací Skautis?
     * @var bool
     */
    private $isTestMode = TRUE;

    /**
     * Cachovat WSDL
     * @var bool
     */
    private $cache;

    /**
     *
     * @var bool
     */
    public $profiler;

    public function isCompression() {
        return $this->compression;
    }

    public function setCompression($compression) {
        $this->compression = $compression;
        return $this;
    }

    public function isTestMode() {
        return $this->isTestMode;
    }

    public function setTestMode($isTestMode) {
        $this->isTestMode = (bool) $isTestMode;
        return $this;
    }

    /**
     *
     * @return array
     */
    public function getSoapArguments()
    {
	$soapOpts = [];
        $soapOpts['soap_version'] = SOAP_1_2;
        $soapOpts['encoding'] = 'utf-8';
        if ($this->compression === TRUE)
            $soapOpts['compression'] = SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP;


        if ($this->cache === TRUE)
            $soapOpts['compression'] = SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP;

	return $soapOpts;
    }


    /**
     * @return bool
     */
    public function isProfiling() {
	return $this->profiler;
    }

    /**
     * Zapne cachovani WSDL
     */
    public function enableCache() {
	$this->cache = TRUE;
    }

    /**
     * Vypne cachovani WSDL
     */
    public function disableCache() {
	$this->cache = FALSE;
    }

    /**
     * Zjisti jestli je WSDL cachovane
     */
    public function isCacheEnabled() {
	return $this->cache;
    }

    /**
     * vrací začátek URL adresy
     * @return string
     */
    public function getHttpPrefix()
    {
        return $this->isTestMode ? self::HTTP_PREFIX_TEST : self::HTTP_PREFIX;
    }
}
