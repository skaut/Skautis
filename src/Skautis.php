<?php

namespace Skautis;

use Skautis\SkautisQuery;
use Skautis\Factory\WSFactory;
use Skautis\Factory\BasicWSFactory;
use Skautis\SessionAdapter\AdapterInterface;
use Skautis\SessionAdapter\SessionAdapter;
use Skautis\SessionAdapter\FakeAdapter;
use Skautis\Exception\AbortException;
use Skautis\Exception\InvalidArgumentException;
use Skautis\Exception\WsdlException;
use Exception;

/**
 * @author Hána František <sinacek@gmail.com>
 * Singleton
 */
class Skautis {

// <editor-fold defaultstate="collapsed" desc="vars">

    const APP_ID = "ID_Application";
    const TOKEN = "ID_Login";
    const ID_ROLE = "ID_Role";
    const ID_UNIT = "ID_Unit";
    const LOGOUT_DATE = "LOGOUT_Date";
    const AUTH_CONFIRMED = "AUTH_Confirmed";
    const HTTP_PREFIX_TEST = "http://test-is";
    const HTTP_PREFIX = "https://is";
    CONST SESSION_ID = "skautis_library_data";

    /**
     * sigleton
     * @var Skautis
     */
    private static $instance = NULL;

    /**
     * aliasy pro wdsl
     * @var array
     */
    private $aliases = array(
        "user" => "UserManagement",
        "usr" => "UserManagement",
        "org" => "OrganizationUnit",
        "app" => "ApplicationManagement",
        "event" => "Events",
        "events" => "Events",
    );

    /**
     * dostupné WSDL Skautisu
     * @var array
     */
    private $wsdl = array(
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
    );

    /**
     * pole aktivních Skautis\WS
     * @var array(Skautis\WS)
     */
    private $active = array();

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

    /**
     *
     * @var bool
     */
    public $profiler;

    /**
     * @var WSFactory
     */
    protected $wsFactory = NULL;

    /**
     * @var AdapterInterface
     */
    protected $sessionAdapter = NULL;

// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="getters & setters">

    public function isCompression() {
        return $this->compression;
    }

    public function setCompression($compression) {
        $this->compression = $compression;
        return $this;
    }

    public function IsTestMode() {
        return $this->isTestMode;
    }

    public function setTestMode($isTestMode) {
        $this->isTestMode = (bool) $isTestMode;
        return $this;
    }

    public function setAppId($appId) {
        $this->perStorage->init[self::APP_ID] = $appId;
        $this->writeConfigToSession();
        return $this;
    }

    public function getAppId() {
        return isset($this->perStorage->init[self::APP_ID]) ? $this->perStorage->init[self::APP_ID] : null;
    }

    public function isSetAppId() {
        return $this->getAppId() != NULL ? TRUE : FALSE;
    }

    public function getToken() {
        return isset($this->perStorage->init[self::TOKEN]) ? $this->perStorage->init[self::TOKEN] : null;
    }

    public function setToken($token) {
        $this->perStorage->init[self::TOKEN] = $token;
        $this->active = array(); //zmenilo se prihlašování
        $this->writeConfigToSession();
        return $this;
    }

    /**
     * alias of getToken()
     * @return string
     */
    public function getLoginId() {
        return $this->getToken();
    }

    public function getRoleId() {
        return isset($this->perStorage->data[self::ID_ROLE]) ? $this->perStorage->data[self::ID_ROLE] : NULL;
    }

    public function setRoleId($roleId) {
        $this->perStorage->data[self::ID_ROLE] = (int) $roleId;
        $this->writeConfigToSession();
        return $this;
    }

    public function getUnitId() {
        return isset($this->perStorage->data[self::ID_UNIT]) ? $this->perStorage->data[self::ID_UNIT] : NULL;
    }

    public function setUnitId($unitId) {
        $this->perStorage->data[self::ID_UNIT] = (int) $unitId;
        $this->writeConfigToSession();
        return $this;
    }

    /**
     * Vraci datum a cas automaticeho odhlaseni z is.skaut.cz
     *
     * @return \DateTime
     */
    public function getLogoutDate() {
        return isset($this->perStorage->data[self::LOGOUT_DATE]) ? $this->perStorage->data[self::LOGOUT_DATE] : NULL;
    }

    /**
     *
     * Nastavi cas automatickeho odhlaseni z is.skaut.cz
     *
     * @param \DateTime $logoutDate
     *
     * @return void
     */
    public function setLogoutDate(\DateTime $logoutDate) {
        $this->perStorage->data[self::LOGOUT_DATE] = $logoutDate;
        $this->writeConfigToSession();
        return $this;
    }


    /**
     * nastavuje trvalé úložiště
     *
     * @throws InvalidArgumentException
     * @deprecated
     */
    public function setStorage() {
        throw new \BadFunctionCallException("Tato funkce jiz neni podporovana, pouzijte SessionAdapter");
    }

    /**
     * Inicializuje $this->perStorage
     */
    protected function initEmptyConfig() {
        $this->perStorage = new \StdClass();
        $this->perStorage->init = array();
        $this->perStorage->data = array();
    }

// </editor-fold>

    public function __construct($appId = NULL, $testMode = FALSE, $profiler = FALSE, AdapterInterface $sessionAdapter = NULL, WSFactory $wsFactory = NULL) {

        if (!is_bool($testMode)) {
            throw new InvalidArgumentException('Argument $testMode ma spatnou hodnotu: ' . print_r($testMode, TRUE));
        }

        if (!is_bool($profiler)) {
            throw new InvalidArgumentException('Argument $profiler ma spatnou hodnotu: ' . print_r($profiler, TRUE));
	}


        if ($sessionAdapter !== NULL) {
            $this->sessionAdapter = $sessionAdapter;

            if ($this->sessionAdapter->has(self::SESSION_ID)) {
		$this->perStorage = $this->sessionAdapter->get(self::SESSION_ID);
            }

        }
	else {
            $this->sessionAdapter = new FakeAdapter();
	}

	if ($this->perStorage === NULL) {
	    $this->initEmptyConfig();
	}

	if ($appId !== NULL) {
            $this->setAppId($appId);
        }

        $this->setTestMode($testMode);
	$this->profiler = $profiler;

        if ($wsFactory === NULL) {
            $this->wsFactory = new BasicWSFactory();
        }
	else {
	    $this->wsFactory = $wsFactory;
	}

	$this->onEvent[] = array($this, 'addLogQuery');

        $this->writeConfigToSession();
    }

    /**
     * Ziska sdilenou instanci Skautis objektu
     *
     * Ano vime ze to neni officialni pattern
     * Jedna se o kockopsa Mezi singletonem a StaticFactory
     * Factory metoda na stride kterou instantizuje a novy objekt vytvari jen 1x za beh
     * Proc to tak je? Ohled na zpetnou kompatibilitu a out of the box pouzitelnost pro amatery
     *
     * @var string $appId nastavení appId (nepovinné)
     * @var bool $testMode funguje v testovacím provozu? - výchozí je testovací mode (nepovinné)
     * @var bool $profiler ma uchovavat data pro profilovani?
     *
     * @return Skautis Sdilena instance Skautis knihovny pro cely beh PHP skriptu
     * @throws InvalidArgumentException
     */
    public static function getInstance($appId = NULL, $testMode = FALSE, $profiler = FALSE, AdapterInterface $sessionAdapter = NULL, WSFactory $wsFactory = NULL) {



	if (self::$instance === NULL) {

   	    // Out of box integrace s $_SESSION
            if ($sessionAdapter === NULL) {
	        $sessionAdapter = new SessionAdapter();
	    }

            self::$instance = new self($appId, $testMode, $profiler, $sessionAdapter, $wsFactory);
        }


        return self::$instance;
    }

    /**
     * @param string $name
     * @return WS
     * @throws AbortException
     */
    public function __get($name) {
        if (!isset($this->perStorage->init[self::APP_ID])) {
            throw new AbortException("ID_Application is not set");
        }

        $wsdlName = $this->getWsdl($name);

        $wsdlKey = $wsdlName;
        if ($this->isTestMode) {
            $wsdlKey .= '_Test';
        }

        if (!isset($this->active[$wsdlKey])) {
            $this->active[$wsdlKey] = $this->wsFactory->createWS($this->getWsdlUri($wsdlName), $this->perStorage->init, $this->compression, $this->profiler);
            if ($this->profiler) {
                $this->active[$wsdlKey]->onEvent = $this->onEvent;
            }
        }
        return $this->active[$wsdlKey];
    }

    protected function getWsdlUri($wsdlName) {
        return $this->getHttpPrefix() . ".skaut.cz/JunakWebservice/" . $wsdlName . ".asmx?WSDL";
    }

    /**
     * @param string $name
     * @return string
     * @throws WsdlException
     */
    protected function getWsdl($name) {
        if (array_key_exists($name, $this->wsdl)) { //hleda podle celeho nazvu
            return $name;
        }
        if ((array_key_exists($name, $this->aliases))) {//podle aliasu
            return $this->aliases[$name];
        }
        throw new WsdlException("Invalid WSDL: " . $name);
    }

    /**
     * vrací url na přihlášení
     * @param string $backlink
     * @return string url
     */
    public function getLoginUrl($backlink = null) {
        return $this->getHttpPrefix() . ".skaut.cz/Login/?appid=" . $this->getAppId() . (isset($backlink) ? "&ReturnUrl=" . $backlink : "");
    }

    /**
     * vrací url na odhlášení
     * @return string url
     */
    public function getLogoutUrl() {
        return $this->getHttpPrefix() . ".skaut.cz/Login/LogOut.aspx?appid=" . $this->getAppId() . "&token=" . $this->getToken();
    }
    /**
     * vrací url k registraci
     * @return string url
     */
    public function getRegisterUrl() {
        return $this->getHttpPrefix() . ".skaut.cz/Login/Registration.aspx?appid=" . $this->getAppId() . (isset($backlink) ? "&ReturnUrl=" . $backlink : "");
    }


    /**
     * vrací začátek URL adresy
     * @return string
     */
    public function getHttpPrefix() {
        return $this->isTestMode ? self::HTTP_PREFIX_TEST : self::HTTP_PREFIX;
    }

    /**
     * Kontoluje jestli je přihlášení platné
     *
     * @param bool $hardCheck Vynuti kontrolu prihlaseni na serveru
     * @return bool
     */
    public function isLoggedIn($hardCheck = FALSE) {

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

    protected function isAuthConfirmed() {
        if (!isset($this->perStorage->data[self::AUTH_CONFIRMED]))
            return FALSE;

	return $this->perStorage->data[self::AUTH_CONFIRMED];
    }


    protected function setAuthConfirmed($isConfirmed) {
	$this->perStorage->data[self::AUTH_CONFIRMED] = (bool) $isConfirmed;
    }

    protected function confirmAuth() {
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
    function updateLogoutTime() {
        $this->user->LoginUpdateRefresh(array("ID" => $this->getToken()));
    }

    /**
     * zkontroluje platnost tokenu a prodlouží přihlášení o 30 min
     * @return bool
     */
    function checkLoginToken() {
        return $this->isLoggedIn();
    }

    /**
     * Hromadne nastaveni po prihlaseni
     *
     * @param array $data Pole dat zaslanych skautisem (napriklad $_SESSION)
     */
    public function setLoginData(array $data) {

	$token = isset($data['skautIS_Token']) ? $data['skautIS_Token'] : "";
	$this->setToken($token);

	$roleId = isset($data['skautIS_IDRole']) ? $data['skautIS_IDRole'] : "";
        $this->setRoleId($roleId);

	$unitId = isset($data['skautIS_IDUnit']) ? $data['skautIS_IDUnit'] : "";
        $this->setUnitId($unitId);

	if (!isset($data['skautIS_DateLogout'])) {
	    return;
	}

        $logoutDate = \DateTime::createFromFormat('j. n. Y H:i:s', $data['skautIS_DateLogout']);
	$this->setLogoutDate($logoutDate);

    }

    /**
     * hromadny reset dat po odhlaseni
     */
    public function resetLoginData() {
        $this->setLoginData(array());
        $this->perStorage->data[self::LOGOUT_DATE] = null;
    }

    /**
     * vrací seznam WSDL, které podporuje
     * @return array
     */
    public function getWsdlList() {
        $wsdlNames = array_keys($this->wsdl);
        return array_combine($wsdlNames, $wsdlNames);
    }

    /**
     * ověřuje, zda je Skautis odstaven pro údržbu
     * @return boolean
     */
    public function isMaintenance() {
        if ((@simplexml_load_file($this->getWsdlUri("UserManagement"))->wsdl) === null) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Nastavi WSFactory
     *
     * @param $wsFactory WSFactory
     */
    public function setWSFactory(WSFactory $wsFactory) {
        $this->wsFactory = $wsFactory;
    }

    /**
     * Ulozi nastaveni do session
     *
     * @return void
     */
    protected function writeConfigToSession() {
        $this->sessionAdapter->set(self::SESSION_ID, $this->perStorage);
    }

    /**
     * Nastavi uloziste session dat
     *
     * @param AdapterInterface $sessionAdapter Objekt zprostredkovavajici ukladani do session
     *
     * @return void
     */
    public function setAdapter(AdapterInterface $sessionAdapter)
    {
        $this->sessionAdapter = $sessionAdapter;
        $this->writeConfigToSession();
    }

    public function addLogQuery(SkautisQuery $query)
    {
	$this->log[] = $query;
    }

    /**
     * @return bool
     */
    public function isProfiling() {
	return $this->profiler;
    }
}
