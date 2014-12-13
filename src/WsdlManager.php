<?php

namespace Skautis;

use Skautis\Factory\WSFactory;
use Skautis\Exception\WsdlException;
use Skautis\WS;

/**
 * Trida pro spravu WSDL a WS
 */
class WsdlManager
{

     /**
      * @var WSFactory
      */
     protected $WSFactory;

     /**
      * @var string
      */
     protected $httpPrefix;

     /**
      * @var bool
      */
     protected $compression;

     /**
      * @var bool
      */
     protected $profiler;


     /**
      * Aliasy WSDL pro rychly pristup
      *
      * @var array
      */
     protected $aliases = [
        "user" => "UserManagement",
        "usr" => "UserManagement",
        "org" => "OrganizationUnit",
        "app" => "ApplicationManagement",
        "event" => "Events",
        "events" => "Events",
    ];

    /**
     * dostupné WSDL Skautisu
     * @var array
     */
    protected $wsdl = [
        "ApplicationManagement" => null,
        "ContentManagement" => null,
        "Evaluation" => null,
        "Events" => null,
        "Exports" => null,
        "GoogleApps" => null,
        "Journal" => null,
        "Material" => null,
        "Message" => null,
        "OrganizationUnit" => null,
        "Power" => null,
        "Reports" => null,
        "Summary" => null,
        "Telephony" => null,
        "UserManagement" => null,
        "Vivant" => null,
        "Welcome" => null,
    ];

    /**
     * pole aktivních Skautis\WS
     * @var array(Skautis\WS)
     */
    protected $active = [];


    /**
     * Konstruktor
     *
     * @param WSFactory $factory     Pro vytvareni WS objektu
     * @param string    $httpPrefix  Http prefix
     * @param bool      $compression Komprese
     * @param bool      $profiler    Profilovani
     */
    public function __construct(WSFactory $factory, $httpPrefix, $compression, $profiler)
    {
        $this->WSFactory = $factory;
	$this->httPrefix = $httpPrefix;
	$this->compression = $compression;
	$this->profiler = $profiler;
    }

    /**
     * Ziska WS
     *
     * @param string $name   Jmeno nebo Alias WSDL
     * @param array  $config SoapClient parameters
     *
     * @return WS
     */
    public function getWsdl($name, array $config = [])
    {
        $wsdlName = $this->getWsdlName($name);

        $wsdlKey = $wsdlName;
        if ($this->isTestMode) {
            $wsdlKey .= '_Test';
        }

        if (!isset($this->active[$wsdlKey])) {
            $this->active[$wsdlKey] = $this->wsFactory->createWS($this->getWsdlUri($wsdlName), $config, $this->compression, $this->profiler);
            if ($this->profiler) {
                $this->active[$wsdlKey]->onEvent = $this->onEvent;
            }
        }
        return $this->active[$wsdlKey];
    }

    /**
     * Ziska cele jmeno WSDL souboru
     *
     * @param string $name Jmeno nebo Alias WSDL
     *
     * @return string
     * @throws WsdlException
     */
    protected function getWsdlName($name)
    {
        if (array_key_exists($name, $this->wsdl)) { //hleda podle celeho nazvu
            return $name;
        }
        if ((array_key_exists($name, $this->aliases))) {//podle aliasu
            return $this->aliases[$name];
        }
        throw new WsdlException("Invalid WSDL: " . $name);
    }

    /**
     * vrací seznam WSDL, které podporuje
     *
     * @return array
     */
    public function getWsdlList()
    {
        $wsdlNames = array_keys($this->wsdl);
	return array_combine($wsdlNames, $wsdlNames);
    }

    /**
     * Ziska URL adresu WSDL
     *
     * @param string $wsdlName Cele jmeno WSDL
     *
     * @return string
     */
    protected function getWsdlUri($wsdlName)
    {
        return $this->httpPrefix . ".skaut.cz/JunakWebservice/" . $wsdlName . ".asmx?WSDL";
    }

}
