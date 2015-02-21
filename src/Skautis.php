<?php

namespace Skautis;

use Skautis\Wsdl\WsdlManager;
use Skautis\Wsdl\WebService;

/**
 * Třída pro práci se skautISem
 *
 * Sdružuje všechny komponenty a zprostředkovává jejich komunikaci.
 *
 * @author Hána František <sinacek@gmail.com>
 */
class Skautis
{

    use HelperTrait;

    /**
     * @var WsdlManager
     */
    private $wsdlManager;

    /**
     * @var User
     */
    private $user;

    /**
     * @var SkautisQuery[]
     */
    private $log;


    /**
     * @param WsdlManager $wsdlManager
     * @param User $user
     */
    public function __construct(WsdlManager $wsdlManager, User $user)
    {
        $this->wsdlManager = $wsdlManager;
        $this->user = $user;
    }

    /**
     * @return WsdlManager
     */
    public function getWsdlManager()
    {
        return $this->wsdlManager;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->wsdlManager->getConfig();
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Získá objekt webové služby
     *
     * @param string $name
     * @return WebService|mixed
     */
    public function getWebService($name)
    {
        return $this->wsdlManager->getWebService($name, $this->user->getLoginId());
    }

    /**
     * Trocha magie pro snadnější přístup k webovým službám.
     *
     * @param string $name
     * @return WebServiceInterface|mixed
     */
    public function __get($name)
    {
        return $this->getWebService($name);
    }

    /**
     * Vrací URL na přihlášení
     *
     * @param string|null $backlink
     * @return string
     */
    public function getLoginUrl($backlink = null)
    {
        $query = [];
        $query['appid'] = $this->getConfig()->getAppId();
        if (!empty($backlink)) {
            $query['ReturnUrl'] = $backlink;
        }
        return $this->getConfig()->getBaseUrl() . "Login/?" . http_build_query($query, '', '&');
    }

    /**
     * Vrací URL na odhlášení
     *
     * @return string
     */
    public function getLogoutUrl()
    {
        $query = [];
        $query['appid'] = $this->getConfig()->getAppId();
        $query['token'] = $this->user->getLoginId();
        return $this->getConfig()->getBaseUrl() . "Login/LogOut.aspx?" . http_build_query($query, '', '&');
    }

    /**
     * Vrací URL k registraci
     *
     * @param string|null $backlink
     * @return string
     */
    public function getRegisterUrl($backlink = null)
    {
        $query = [];
        $query['appid'] = $this->getConfig()->getAppId();
        if (!empty($backlink)) {
            $query['ReturnUrl'] = $backlink;
        }
        return $this->getConfig()->getBaseUrl() . "Login/Registration.aspx?" . http_build_query($query, '', '&');
    }

    /**
     * Hromadné nastavení po přihlášení
     *
     * @param array $data
     */
    public function setLoginData(array $data)
    {
        $data = Helpers::parseLoginData($data);
        $this->getUser()->setLoginData($data[User::ID_LOGIN], $data[User::ID_ROLE], $data[User::ID_UNIT], $data[User::LOGOUT_DATE]);
    }

    /**
     * Ověřuje, zda je skautIS odstaven pro údržbu
     *
     * @return boolean
     */
    public function isMaintenance()
    {
        return $this->wsdlManager->isMaintenance();
    }

    /**
     * Zapne logování všech SOAP callů
     */
    public function enableDebugLog()
    {
        if ($this->log !== null) {
            // Debug log byl již zapnut dříve.
            return;
        }

        $this->log = [];
        $logger = function (SkautisQuery $query) {
            $this->log[] = $query;
        };
        $this->wsdlManager->addWebServiceListener(WebService::EVENT_SUCCESS, $logger);
        $this->wsdlManager->addWebServiceListener(WebService::EVENT_FAILURE, $logger);
    }

    /**
     * Vrací zalogované SOAP cally
     *
     * @return SkautisQuery[]
     */
    public function getDebugLog()
    {
        return ($this->log !== null) ? $this->log : [];
    }
}
