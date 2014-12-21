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
     protected $wsFactory;

     /**
      * @var Config
      */
     protected $config;

     /**
      * @var array
      */
     protected $wsListeners = [];


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
        $this->wsFactory = $factory;
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
            $this->active[$wsdlKey] = $this->createWs($wsdlName, $config);
        }
        return $this->active[$wsdlKey];
    }

    protected function createWs($wsdlName, $config)
    {
        $ws = $this->wsFactory->createWS($this->getWsdlUri($wsdlName), $config);

        foreach ($this->wsListeners as $listener) {
            $ws->subscribe($listener['event_name'], $listener['callback']);
        }

        return $ws;
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

    public function isMaintenance()
    {
        $headers = get_headers($this->getWsdlUri("UserManagement"));
        return !in_array('HTTP/1.1 200 OK', $headers);
    }

    public function addWsListener($eventName, callable $callback)
    {
        $this->wsListeners[] = [
		'event_name' => $eventName,
		'callback' => $callback,
		];
    }
}
