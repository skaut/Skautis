<?php

@(include_once 'SkautIS_WS.php');
@(include_once 'SkautIS_exceptions.php');

/**
 * @author sinacek
 * Singleton
 */
class SkautIS {
// <editor-fold defaultstate="collapsed" desc="vars">
    const APP_ID = "ID_Application";
    const TOKEN = "ID_Login";
    const ID_ROLE = "ID_Role";
    const ID_UNIT = "ID_Unit";

    const HTTP_PREFIX_TEST = "http://test-is";
    const HTTP_PREFIX = "https://is";

    /**
     * sigleton
     * @var SkautIS 
     */
    private static $instance;

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
     * dostupné WSDL SkautISu
     * @var array
     */
    private $wsdl = array(
        "UserManagement" => null,
        "OrganizationUnit" => null,
        "Events" => null,
        "ApplicationManagement" => null,
        "Evaluation" => null,
        "Exports" => null,
        "GoogleApps" => null,
        "Journal" => null,
        "Message" => null,
        "Reports" => null,
        "Summary" => null,
        "Telephony" => null,
        "Welcome" => null,
    );
    
    /**
     * pole aktivních SkautIS_WS
     * @var array(SkautIS_WS)
     */
    private $active = array();

    /**
     * používat kompresi?
     * @var bool
     */
    private $compression = TRUE;

    /**
     * používat testovací skautIS?
     * @var bool
     */
    private $isTestMode = TRUE;

    /**
     * persistentní pole
     * ['init'] - obsahuje self::APP_ID a self::TOKEN
     * ['data'] - obsahuje cokoliv dalšího
     * @var array 
     */
    private $perStorage;

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
        return $this;
    }

    public function getAppId() {
        return $this->perStorage->init[self::APP_ID];
    }

    public function isSetAppId() {
        return $this->getAppId() != NULL ? TRUE : FALSE;
    }

    public function getToken() {
        return $this->perStorage->init[self::TOKEN];
    }

    public function setToken($token) {
        $this->perStorage->init[self::TOKEN] = $token;
        $this->active = array(); //změnilo se přihlašování
        return $this;
    }

    /**
     * alias of getToken()
     * @return type 
     */
    public function getLoginId() {
        return $this->getToken();
    }

    public function getRoleId() {
        return $this->perStorage->data[self::ID_ROLE];
    }

    public function setRoleId($roleId) {
        $this->perStorage->data[self::ID_ROLE] = (int) $roleId;
        return $this;
    }

    public function getUnitId() {
        return $this->perStorage->data[self::ID_UNIT];
    }

    public function setUnitId($unitId) {
        $this->perStorage->data[self::ID_UNIT] = (int) $unitId;
        return $this;
    }

    public function setStorage(&$storage) {
        $this->perStorage = $storage;
    }

// </editor-fold>

    private function __construct() {
        //@todo: předělat bez závislosti na Nette - hledání vadného přihlašování
//        $this->perStorage = &$_SESSION["__" . __CLASS__]; //defaultni persistentní uloziste
        $this->perStorage = Environment::getSession()->getSection("__" . __CLASS__); //defaultni persistentní uloziste
        if (defined("SkautIS_ID_Application"))
            $this->setAppId(SkautIS_ID_Application);
    }

    /**
     * Singleton
     * @var bool $appId nastavení appId (nepovinné)
     * @var bool $testMode funguje v testovacím provozu? - výchozí je testovací mode (nepovinné)
     * @return SkautIS
     */
    public static function getInstance($appId = NULL, $testMode = false) {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self;
        }
        if ($appId !== NULL)
            self::$instance->setAppId($appId);
        self::$instance->setTestMode($testMode);

        return self::$instance;
    }

    public function __get($name) {
        if (!isset($this->perStorage->init[self::APP_ID])) {
            throw new SkautIS_AbortException("ID_Application is not set");
        }

        $wsdlName = $this->getWsdl($name);

        if (!isset($this->active[$wsdlName])) {
            $wsdl = ($this->isTestMode ? self::HTTP_PREFIX_TEST : self::HTTP_PREFIX) . ".skaut.cz/JunakWebservice/" . $wsdlName . ".asmx?WSDL";
            $this->active[$wsdlName] = new SkautIS_WS($wsdl, $this->perStorage->init, $this->compression);
        }
        return $this->active[$wsdlName];
    }

    protected function getWsdl($name) {
        if (array_key_exists($name, $this->wsdl)) { //hleda podle celeho nazvu
            return $name;
        }
        if ((array_key_exists($name, $this->aliases))) {//podle aliasu
            return $this->aliases[$name];
        }
        throw new SkautIS_WsdlException("Invalid WSDL: " . $name);
    }

    /**
     * vrací url na přihlášení
     * @param string $backlink
     * @return url 
     */
    public function getLoginUrl($backlink) {
        return $this->getHttpPrefix() . ".skaut.cz/Login/?appid=" . $this->getAppId() . (isset($backlink) ? "&ReturnUrl=" . $backlink : "");
    }

    /**
     * vrací url na odhlášení
     * @return url
     */
    public function getLogoutUrl() {
        return $this->getHttpPrefix() . ".skaut.cz/Login/LogOut.aspx?appid=" . $this->getAppId() . "&token=" . $this->getToken();
    }
    
    /**
     * vrací začátek URL adresy
     * @return string
     */
    public function getHttpPrefix(){
        return $this->isTestMode ? self::HTTP_PREFIX_TEST : self::HTTP_PREFIX;
    }

    /**
     * kontoluje jestli je přihlášení platné
     * @return bool 
     */
    public function isLoggedIn() {
        if ($this->getToken() != NULL && $this->user->userDetail()->ID != NULL)
            return TRUE;
        return FALSE;
    }

    /**
     * prodloužení přihlášení o 30 min
     * @param int $time
     */
    function updateLogoutTime() {
        $this->user->LoginUpdateRefresh(array("ID" => $this->getToken()));
    }

    /**
     * vrací seznam WSDL, které podporuje
     * @return array
     */
    public function getWsdlList() {
        $ret = array();
        foreach ($this->wsdl as $key => $value) {
            $ret[$key] = $key;
        }
        return $ret;
    }

}