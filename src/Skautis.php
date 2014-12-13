<?php

namespace Skautis;

use Skautis\WsdlManager;
use Skautis\HelperTrait;
use Skautis\Config;
use Skautis\SkautisQuery;
use Skautis\Factory\WSFactory;
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
     * persistentní pole
     * ['init'] - obsahuje self::APP_ID a self::TOKEN
     * ['data'] - obsahuje cokoliv dalšího
     * @var \StdClass
     */
    private $perStorage = NULL;

    /**
     * Pole callbacku ktere Skautis preda WS objektu pro debugovani pozadavku na server
     *
     * @var callable[]
     */
    public $onEvent = array();

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
            $this->perStorage = $this->sessionAdapter->get(self::SESSION_ID);
        }

	if ($this->perStorage === NULL) {
	    $this->initEmptyConfig();
	}

	$this->perStorage->init[self::APP_ID] = $config->getAppId();
	$this->wsdlManager = $wsdlManager;
	$this->config = $config;

	$this->onEvent[] = array($this, 'addLogQuery');

        $this->writeConfigToSession();
    }




    /**
     * @return string
     */
    public function getLoginId()
    {
        return isset($this->perStorage->init[self::TOKEN]) ? $this->perStorage->init[self::TOKEN] : null;
    }

    public function getRoleId()
    {
        return isset($this->perStorage->data[self::ID_ROLE]) ? $this->perStorage->data[self::ID_ROLE] : NULL;
    }


    public function getUnitId()
    {
        return isset($this->perStorage->data[self::ID_UNIT]) ? $this->perStorage->data[self::ID_UNIT] : NULL;
    }


    /**
     * Vraci datum a cas automaticeho odhlaseni z is.skaut.cz
     *
     * @return \DateTime
     */
    public function getLogoutDate()
    {
        return isset($this->perStorage->data[self::LOGOUT_DATE]) ? $this->perStorage->data[self::LOGOUT_DATE] : NULL;
    }


    /**
     * Inicializuje $this->perStorage
     */
    protected function initEmptyConfig()
    {
        $this->perStorage = new \StdClass();
        $this->perStorage->init = array();
        $this->perStorage->data = array();
    }


    /**
     * @param string $name
     * @return WS
     * @throws AbortException
     */
    public function __get($name)
    {
        if (!isset($this->perStorage->init[self::APP_ID])) {
            throw new AbortException("ID_Application is not set");
        }

	$soapOpts = $this->config->getSoapArguments();
	$soapOpts[self::TOKEN] = $this->perStorage->init[self::TOKEN];

	return $this->wsdlManager->getWsdl($name, $soapOpts, $this->config->getProfiler());
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
        return $this->config->getHttpPrefix() . ".skaut.cz/Login/LogOut.aspx?appid=" . $this->config->getAppId() . "&token=" . $this->getToken();
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

	if (empty($this->perStorage->init[self::APP_ID]))
            return FALSE;

        if (empty($this->perStorage->init[self::TOKEN]))
            return FALSE;

        if ($this->getLogoutDate()->getTimestamp() < time())
	    return FALSE;

	if ($hardCheck || !$this->isAuthConfirmed())
            $this->confirmAuth();

	if (!$this->isAuthConfirmed())
	    return FALSE;

	return TRUE;
    }

    protected function isAuthConfirmed()
    {
        if (!isset($this->perStorage->data[self::AUTH_CONFIRMED]))
            return FALSE;

	return $this->perStorage->data[self::AUTH_CONFIRMED];
    }


    protected function setAuthConfirmed($isConfirmed)
    {
	$this->perStorage->data[self::AUTH_CONFIRMED] = (bool) $isConfirmed;
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
     */
    public function updateLogoutTime()
    {
        $this->user->LoginUpdateRefresh(array("ID" => $this->getLoginId()));
    }


    /**
     * Hromadne nastaveni po prihlaseni
     *
     * @param array $data Pole dat zaslanych skautisem (napriklad $_SESSION)
     */
    public function setLoginData(array $data)
    {

	$token = isset($data['skautIS_Token']) ? $data['skautIS_Token'] : "";
        $this->perStorage->init[self::TOKEN] = $token;
	//@TODO
	//$this->active = array(); //zmenilo se prihlašování


	$roleId = isset($data['skautIS_IDRole']) ? $data['skautIS_IDRole'] : "";
        $this->perStorage->data[self::ID_ROLE] = (int) $roleId;

	$unitId = isset($data['skautIS_IDUnit']) ? $data['skautIS_IDUnit'] : "";
        $this->perStorage->data[self::ID_UNIT] = (int) $unitId;

	if (!isset($data['skautIS_DateLogout'])) {
            $this->perStorage->data[self::LOGOUT_DATE] = NULL;
	}
	else {
            $logoutDate = \DateTime::createFromFormat('j. n. Y H:i:s', $data['skautIS_DateLogout']);
            $this->perStorage->data[self::LOGOUT_DATE] = $logoutDate;
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
        $headers = get_headers($this->getWsdlUri("UserManagement"));
        return !in_array('HTTP/1.1 200 OK', $headers);
    }


    /**
     * Ulozi nastaveni do session
     *
     * @return void
     */
    protected function writeConfigToSession()
    {
        $this->sessionAdapter->set(self::SESSION_ID, $this->perStorage);
    }

    public function addLogQuery(SkautisQuery $query)
    {
	$this->log[] = $query;
    }


}
