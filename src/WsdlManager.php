<?php

namespace Skautis;

use Skautis\Factory\WSFactory;
use Skautis\Exception\WsdlException;
use Skautis\Config;
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
      * @var Config
      */
     protected $config;


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
     * @param WSFactory $factory Pro vytvareni WS objektu
     * @param Config    $config  Konfigurace
     */
    public function __construct(WSFactory $factory, Config $config)
    {
        $this->WSFactory = $factory;
        $this->config = $config;
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
        if ($this->config->getTestMode()) {
            $wsdlKey .= '_Test';
        }

        if (!isset($this->active[$wsdlKey])) {
            $this->active[$wsdlKey] = $this->wsFactory->createWS($this->getWsdlUri($wsdlName), $config, $this->config->getCompression(), $this->config->getProfiler());
            if ($this->config->getProfiler()) {
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
        return $this->config->getHttpPrefix() . ".skaut.cz/JunakWebservice/" . $wsdlName . ".asmx?WSDL";
    }

}
