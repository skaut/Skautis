<?php

namespace Skautis\Wsdl;

use Skautis\EventDispatcher\EventDispatcherInterface;
use Skautis\Config;
use Skautis\User;

/**
 * Třída pro správu webových služeb SkautISu
 */
class WsdlManager
{

    /**
     * @var WebServiceFactoryInterface
     */
    protected $webServiceFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Aliasy webových služeb pro rychlý přístup
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
     * Dostupné webové služby SkautISu
     *
     * @var array
     */
    protected $supportedWebServices = [
        "ApplicationManagement",
        "ContentManagement",
        "Evaluation",
        "Events",
        "Exports",
        "GoogleApps",
        "Journal",
        "Material",
        "Message",
        "OrganizationUnit",
        "Power",
        "Reports",
        "Summary",
        "Task",
        "Telephony",
        "UserManagement",
        "Vivant",
        "Welcome",
    ];

    /**
     * @var array
     */
    protected $webServiceListeners = [];

    /**
     * Pole aktivních webových služeb
     *
     * @var array
     */
    protected $webServices = [];


    /**
     * @param WebServiceFactoryInterface $webServiceFactory továrna pro vytváření objektů webových služeb
     * @param Config $config
     */
    public function __construct(WebServiceFactoryInterface $webServiceFactory, Config $config)
    {
        $this->webServiceFactory = $webServiceFactory;
        $this->config = $config;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Získá objekt webové služby
     *
     * @param string $name jméno nebo alias webové služby
     * @param string|null $loginId skautIS login token
     * @return WebServiceInterface
     */
    public function getWebService($name, $loginId = null)
    {
        $name = $this->getWebServiceName($name);
        $key = $loginId . '_' . $name . ($this->config->isTestMode() ? '_Test' : '');

        if (!isset($this->webServices[$key])) {
            $options = $this->config->getSoapOptions();
            $options[User::ID_LOGIN] = $loginId;
            $this->webServices[$key] = $this->createWebService($name, $options);
        }

        return $this->webServices[$key];
    }

    /**
     * Vytváří objekt webové služby
     *
     * @param string $name jméno webové služby
     * @param array $options volby pro SoapClient
     * @return WebService|mixed
     */
    public function createWebService($name, array $options = [])
    {
        $webService = $this->webServiceFactory->createWebService($this->getWebServiceUrl($name), $options);

        if ($webService instanceof EventDispatcherInterface) {
            // Zaregistruj listenery na vytvořeném objektu webové služby, pokud je to podporováno
            foreach ($this->webServiceListeners as $listener) {
                $webService->subscribe($listener['eventName'], $listener['callback']);
            }
        }

        return $webService;
    }

    /**
     * Vrací celé jméno webové služby
     *
     * @param string $name jméno nebo alias webové služby
     * @return string
     * @throws WsdlException
     */
    protected function getWebServiceName($name)
    {
        if (in_array($name, $this->supportedWebServices)) {
            // služba s daným jménem existuje
            return $name;
        }
        if (array_key_exists($name, $this->aliases) && in_array($this->aliases[$name], $this->supportedWebServices)) {
            // je definovaný alias pro tuto službu
            return $this->aliases[$name];
        }
        throw new WsdlException("Web service '$name' not found.");
    }

    /**
     * Vrací URL webové služby podle jejího jména
     *
     * @param string $name celé jméno webové služby
     * @return string
     */
    protected function getWebServiceUrl($name)
    {
        return $this->config->getBaseUrl() . "JunakWebservice/" . rawurlencode($name) . ".asmx?WSDL";
    }

    /**
     * Vrací seznam webových služeb, které podporuje
     *
     * @return array
     */
    public function getSupportedWebServices()
    {
        return $this->supportedWebServices;
    }

    /**
     * @return bool
     */
    public function isMaintenance()
    {
        $headers = get_headers($this->getWebServiceUrl("UserManagement"));
        return !in_array('HTTP/1.1 200 OK', $headers);
    }

    /**
     * Přidá listener na spravovaných vytvářených webových služeb.
     *
     * @param string $eventName
     * @param callable $callback
     */
    public function addWebServiceListener($eventName, callable $callback)
    {
        $this->webServiceListeners[] = [
            'eventName' => $eventName,
            'callback' => $callback,
        ];
    }
}
