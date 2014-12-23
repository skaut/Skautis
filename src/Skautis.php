<?php

namespace Skautis;

use Skautis\Exception\InvalidArgumentException;
use Skautis\Wsdl\WsdlManager;
use Skautis\Wsdl\WebService;
use Skautis\SessionAdapter\AdapterInterface;


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

    const APP_ID = "ID_Application";
    const TOKEN = "ID_Login";
    const ID_LOGIN = 'ID_Login';
    const ID_ROLE = "ID_Role";
    const ID_UNIT = "ID_Unit";
    const LOGOUT_DATE = "LOGOUT_Date";
    const AUTH_CONFIRMED = "AUTH_Confirmed";
    const SESSION_ID = "skautis_library_data";

    /**
     * @var WsdlManager
     */
    private $wsdlManager;

    /**
     * @var AdapterInterface
     */
    private $sessionAdapter;

    /**
     * Informace o přihlášení uživatele
     *
     * @var array
     */
    protected $loginData = [];

    /**
     * @var SkautisQuery[]
     */
    private $log;


    /**
     * @param WsdlManager $wsdlManager
     * @param AdapterInterface $sessionAdapter
     */
    public function __construct(WsdlManager $wsdlManager, AdapterInterface $sessionAdapter)
    {
        $this->wsdlManager = $wsdlManager;
        $this->sessionAdapter = $sessionAdapter;

        if ($this->sessionAdapter->has(self::SESSION_ID)) {
            $this->loginData = $this->sessionAdapter->get(self::SESSION_ID);
        }
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
     * Získá objekt webové služby
     *
     * @param string $name
     * @return WebService|mixed
     */
    public function getWebService($name)
    {
        return $this->wsdlManager->getWebService($name, $this->getLoginId());
    }

    /**
     * Trocha magie pro snadnější přístup k webovým službám.
     *
     * @param string $name
     * @return WebService|mixed
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
        $query['token'] = $this->getLoginId();
        return $this->getConfig()->getBaseUrl() . "Login/LogOut.aspx?" . http_build_query($query, '', '&');
    }

    /**
     * Vrací URL k registraci
     *
     * @param string|null $backlink
     * @return string
     */
    public function getRegisterUrl($backlink = "")
    {
        $query = [];
        $query['appid'] = $this->getConfig()->getAppId();
        if (!empty($backlink)) {
            $query['ReturnUrl'] = $backlink;
        }
        return $this->getConfig()->getBaseUrl() . "Login/Registration.aspx?" . http_build_query($query, '', '&');
    }

    /**
     * @return string|null
     */
    public function getLoginId()
    {
        return isset($this->loginData[self::ID_LOGIN]) ? $this->loginData[self::ID_LOGIN] : null;
    }

    /**
     * @return int|null
     */
    public function getRoleId()
    {
        return isset($this->loginData[self::ID_ROLE]) ? $this->loginData[self::ID_ROLE] : null;
    }

    /**
     * @return int|null
     */
    public function getUnitId()
    {
        return isset($this->loginData[self::ID_UNIT]) ? $this->loginData[self::ID_UNIT] : null;
    }

    /**
     * Vrací datum a čas automatického odhlášení ze skautISu
     *
     * @return \DateTime
     */
    public function getLogoutDate()
    {
        return isset($this->loginData[self::LOGOUT_DATE]) ? $this->loginData[self::LOGOUT_DATE] : null;
    }

    /**
     * Hromadné nastavení po přihlášení
     *
     * @param array $data pole dat zaslaných skautISem (například $_SESSION)
     * @throws InvalidArgumentException pokud se nepodaří naparsovat datum
     */
    public function setLoginData(array $data)
    {
        $this->loginData = [];

        if (isset($data['skautIS_Token'])) {
            $this->loginData[self::ID_LOGIN] = $data['skautIS_Token'];
        }

        if (isset($data['skautIS_IDRole'])) {
            $this->loginData[self::ID_ROLE] = (int) $data['skautIS_IDRole'];
        }

        if (isset($data['skautIS_IDUnit'])) {
            $this->loginData[self::ID_UNIT] = (int) $data['skautIS_IDUnit'];
        }

        if (isset($data['skautIS_DateLogout'])) {
            $tz = new \DateTimeZone('Europe/Prague');
            $logoutDate = \DateTime::createFromFormat('j. n. Y H:i:s', $data['skautIS_DateLogout'], $tz);
            if ($logoutDate === false) {
                throw new InvalidArgumentException('Could not parse logout date.');
            }
            $this->loginData[self::LOGOUT_DATE] = $logoutDate;
        }

        $this->writeConfigToSession();
    }

    /**
     * Hromadný reset dat po odhlášení
     */
    public function resetLoginData()
    {
        $this->setLoginData([]);
    }

    /**
     * Kontoluje, jestli je přihlášení platné.
     * Pro správné fungování je nezbytně nutné, aby byl na serveru nastaven správný čas.
     *
     * @param bool $hardCheck vynutí kontrolu přihlášení na serveru
     * @return bool
     */
    public function isLoggedIn($hardCheck = false)
    {
        if (empty($this->loginData[self::ID_LOGIN])) {
            return false;
        }

        if ($hardCheck || !$this->isAuthConfirmed()) {
            $this->confirmAuth();
        }

        return $this->isAuthConfirmed() && $this->getLogoutDate()->getTimestamp() > time();
    }

    /**
     * Bylo potvrzeno přihlášení dotazem na skautIS?
     *
     * @return bool
     */
    protected function isAuthConfirmed()
    {
        return !empty($this->loginData[self::AUTH_CONFIRMED]);
    }

    /**
     * @param bool $isConfirmed
     */
    protected function setAuthConfirmed($isConfirmed)
    {
        $this->loginData[self::AUTH_CONFIRMED] = (bool) $isConfirmed;
        $this->writeConfigToSession();
    }

    /**
     * Potvrdí (a prodlouží) přihlášení dotazem na skautIS.
     */
    protected function confirmAuth()
    {
        try {
            $this->updateLogoutTime();
            $this->setAuthConfirmed(true);
        } catch (\Exception $e) {
            $this->setAuthConfirmed(false);
        }
    }

    /**
     * Prodloužení přihlášení o 30 min
     *
     * @throws InvalidArgumentException pokud se nepodaří naparsovat datum
     */
    public function updateLogoutTime()
    {
        $loginId = $this->getLoginId();
        if ($loginId === null) {
            // Nemáme token, uživatel není přihlášen a není, co prodlužovat
            return;
        }

        $result = $this->getWebService('UserManagement')->LoginUpdateRefresh(array("ID" => $loginId));

        $logoutDate = preg_replace('/\.(\d*)$/', '', $result->DateLogout); //skautIS vrací sekundy včetně desetinné části
        $tz = new \DateTimeZone('Europe/Prague');
        $logoutDate = \DateTime::createFromFormat('Y-m-d\TH:i:s', $logoutDate, $tz);
        if ($logoutDate === false) {
            throw new InvalidArgumentException('Could not parse logout date.');
        }
        $this->loginData[self::LOGOUT_DATE] = $logoutDate;

        $this->writeConfigToSession();
    }

    /**
     * Uloží nastavení do session
     *
     * @return void
     */
    protected function writeConfigToSession()
    {
        $this->sessionAdapter->set(self::SESSION_ID, $this->loginData);
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
