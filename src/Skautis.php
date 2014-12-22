<?php

namespace Skautis;

use Skautis\Wsdl\WsdlManager;
use Skautis\Wsdl\WebService;
use Skautis\SessionAdapter\AdapterInterface;
use Skautis\Exception\AbortException;
use Skautis\Exception\InvalidArgumentException;
use Exception;

/**
 * Trida pro praci se Skautisem
 *
 * Sdruzuje vsechny komponenty a zprostredkovava jejich komunikaci.
 *
 * @author Hána František <sinacek@gmail.com>
 */
class Skautis {

    use HelperTrait;

    const APP_ID = "ID_Application";
    const TOKEN = "ID_Login";
    const ID_ROLE = "ID_Role";
    const ID_UNIT = "ID_Unit";
    const LOGOUT_DATE = "LOGOUT_Date";
    const AUTH_CONFIRMED = "AUTH_Confirmed";
    const SESSION_ID = "skautis_library_data";


    /**
     * @var WsdlManager
     */
    protected $wsdlManager = NULL;

    /**
     * @var AdapterInterface
     */
    protected $sessionAdapter = NULL;

    /**
     * @var Config
     */
    protected $config = NULL;


    /**
     * Informace o prihlaseni uzivatele
     *
     * @var array
     */
    protected $loginData = [];


    /**
     * Pole obsahujici zaznamy ze vsech SOAP callu
     *
     * @var SkautisQuery[]
     */
    public $log = array();


    public function __construct(Config $config, WsdlManager $wsdlManager,  AdapterInterface $sessionAdapter)
    {

        if (!$config->validate()) {
	    throw new InvalidArgumentException('Config neni spravne nastave');
	}


        $this->sessionAdapter = $sessionAdapter;
        if ($this->sessionAdapter->has(self::SESSION_ID)) {
            $this->loginData = $this->sessionAdapter->get(self::SESSION_ID);
        }

	$this->loginData[self::APP_ID] = $config->getAppId();
	$this->wsdlManager = $wsdlManager;
	$this->config = clone $config;

	if ($this->config->getProfiler() == Config::PROFILER_ENABLED) {
            $this->wsdlManager->addWebServiceListener(WebService::EVENT_SUCCESS, array($this, 'addLogQuery'));
            $this->wsdlManager->addWebServiceListener(WebService::EVENT_FAILURE, array($this, 'addLogQuery'));
	}

        $this->writeConfigToSession();
    }




    /**
     * @return string
     */
    public function getLoginId()
    {
        return isset($this->loginData[self::TOKEN]) ? $this->loginData[self::TOKEN] : null;
    }

    public function getRoleId()
    {
        return isset($this->loginData[self::ID_ROLE]) ? $this->loginData[self::ID_ROLE] : NULL;
    }


    public function getUnitId()
    {
        return isset($this->loginData[self::ID_UNIT]) ? $this->loginData[self::ID_UNIT] : NULL;
    }


    /**
     * Vraci datum a cas automaticeho odhlaseni z is.skaut.cz
     *
     * @return \DateTime
     */
    public function getLogoutDate()
    {
        return isset($this->loginData[self::LOGOUT_DATE]) ? $this->loginData[self::LOGOUT_DATE] : NULL;
    }

    /**
     * @param string $name
     * @return WebService
     * @throws AbortException
     */
    public function __get($name)
    {
	$soapOpts = $this->config->getSoapArguments();
	$soapOpts[self::TOKEN] = $this->loginData[self::TOKEN];

	$ws = $this->wsdlManager->getWebService($name, $soapOpts, $this->config->getProfiler());

	return $ws;
    }

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * vrací url na přihlášení
     * @param string $backlink
     * @return string url
     */
    public function getLoginUrl($backlink = "")
    {
        return $this->config->getHttpPrefix() . ".skaut.cz/Login/?appid=" . $this->config->getAppId() . (!empty($backlink) ? "&ReturnUrl=" . $backlink : "");
    }

    /**
     * vrací url na odhlášení
     * @return string url
     */
    public function getLogoutUrl()
    {
        return $this->config->getHttpPrefix() . ".skaut.cz/Login/LogOut.aspx?appid=" . $this->config->getAppId() . "&token=" . $this->getLoginId();
    }
    /**
     * vrací url k registraci
     * @return string url
     */
    public function getRegisterUrl($backlink = "")
    {
        return $this->config->getHttpPrefix() . ".skaut.cz/Login/Registration.aspx?appid=" . $this->config->getAppId() . (!empty($backlink) ? "&ReturnUrl=" . $backlink : "");
    }



    /**
     * Kontoluje jestli je přihlášení platné
     *
     * @param bool $hardCheck Vynuti kontrolu prihlaseni na serveru
     * @return bool
     */
    public function isLoggedIn($hardCheck = FALSE)
    {

        if ($hardCheck || !$this->isAuthConfirmed())
            $this->confirmAuth();

	if (!$this->isAuthConfirmed())
	    return FALSE;

        if (empty($this->loginData[self::TOKEN]))
	    return FALSE;

        if ($this->getLogoutDate()->getTimestamp() < time())
	    return FALSE;

	return TRUE;
    }

    protected function isAuthConfirmed()
    {
        if (!isset($this->loginData[self::AUTH_CONFIRMED]))
            return FALSE;

	return $this->loginData[self::AUTH_CONFIRMED];
    }


    protected function setAuthConfirmed($isConfirmed)
    {
        $this->loginData[self::AUTH_CONFIRMED] = (bool) $isConfirmed;
        $this->writeConfigToSession();
    }

    protected function confirmAuth()
    {
        try {
            $this->updateLogoutTime();
            $this->setAuthConfirmed(true);
        } catch (Exception $ex) {
            $this->setAuthConfirmed(false);
        }
    }

    /**
     * prodloužení přihlášení o 30 min
     *
     * @throws \Exception Pokud se nepodarilo naparsovat LogoutDate
     */
    public function updateLogoutTime()
    {
        $result = $this->user->LoginUpdateRefresh(array("ID" => $this->getLoginId()));

	$dateStr = preg_replace('/\.(\d+)/', '+00:00', $result->DateLogout);
	$logoutDate = \DateTime::createFromFormat(\DateTime::ISO8601, $dateStr);

	if ($logoutDate === false)
	    throw  new Exception('Nepodarilo se naparsovat datum odhlaseni');

        $this->loginData[self::LOGOUT_DATE] = $logoutDate;
    }


    /**
     * Hromadne nastaveni po prihlaseni
     *
     * @param array $data Pole dat zaslanych skautisem (napriklad $_SESSION)
     */
    public function setLoginData(array $data)
    {
	$this->loginData = [];

	$token = isset($data['skautIS_Token']) ? $data['skautIS_Token'] : "";
        $this->loginData[self::TOKEN] = $token;
	//@TODO Zmenil se token, vytvorena WS uz by se nemela pouzivat.


	$roleId = isset($data['skautIS_IDRole']) ? $data['skautIS_IDRole'] : "";
        $this->loginData[self::ID_ROLE] = (int) $roleId;

	$unitId = isset($data['skautIS_IDUnit']) ? $data['skautIS_IDUnit'] : "";
        $this->loginData[self::ID_UNIT] = (int) $unitId;

	if (!isset($data['skautIS_DateLogout'])) {
            $this->loginData[self::LOGOUT_DATE] = NULL;
	}
	else {
            $logoutDate = \DateTime::createFromFormat('j. n. Y H:i:s', $data['skautIS_DateLogout']);
            $this->loginData[self::LOGOUT_DATE] = $logoutDate;
	}

        $this->writeConfigToSession();
    }

    /**
     * hromadny reset dat po odhlaseni
     */
    public function resetLoginData()
    {
        $this->setLoginData(array());
    }

    /**
     * ověřuje, zda je Skautis odstaven pro údržbu
     * @return boolean
     */
    public function isMaintenance()
    {
        return $this->wsdlManager->isMaintenance();
    }


    /**
     * Ulozi nastaveni do session
     *
     * @return void
     */
    protected function writeConfigToSession()
    {
        $this->sessionAdapter->set(self::SESSION_ID, $this->loginData);
    }

    public function addLogQuery(SkautisQuery $query)
    {
	$this->log[] = $query;
    }
}
