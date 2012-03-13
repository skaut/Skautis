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

    /**
     * sigleton
     * @var SkautIS 
     */
    private static $instance;

    /**
     * uchovává data pouze během jednoho požadavku
     * @var type 
     */
    private $temp;
    
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
        "Journal" => null,
        "Message" => null,
        "Reports" => null,
        "Summary" => null,
        "Telephony" => null,
        "Welcome" => null,
    );

    /**
     * pole aktivních WS
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
        $this->perStorage = &$_SESSION["__" . __CLASS__]; //defaultni persistentní uloziste
        if(defined("SkautIS_ID_Application"))
            $this->setAppId (SkautIS_ID_Application);
    }

    /**
     * Singleton
     * @var bool $appId nastavení appId (nepovinné)
     * @var bool $testMode funguje v testovacím provozu? - výchozí je testovací mode (nepovinné)
     * @return SkautIS
     */
    public static function getInstance($appId = NULL, $testMode = NULL) {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self;
        }
        if ($appId)
            self::$instance->setAppId($appId);
        if ($testMode)
            self::$instance->setTestMode($testMode);
            
        return self::$instance;
    }

    public function __get($name) {
        if (!isset($this->perStorage->init[self::APP_ID])) {
            throw new SkautIS_AbortException("ID_Application is not set");
        }

        $wsdlName = $this->getWsdl($name);

        if (!isset($this->active[$wsdlName])) {
            $wsdl = ($this->isTestMode ? "http://test-is" : "https://is") . ".skaut.cz/JunakWebservice/" . $wsdlName . ".asmx?WSDL";
            $this->active[$wsdlName] = new SkautIS_WS($wsdl, $this->perStorage->init, $this->compression);
        }
        return $this->active[$wsdlName];
    }

    public function getWsdl($name) {
//        dump($name);
//        dump($this->aliases);
//        dump($this->wsdl);die();
        if (array_key_exists($name, $this->wsdl)) { //hleda podle celeho nazvu
            return $name;
        }
        if((array_key_exists($name, $this->aliases))) {//podle aliasu
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
        return ($this->isTestMode ? "http://test-is" : "https://is") . ".skaut.cz/Login/?appid=" . $this->getAppId() . (isset($backlink) ? "&ReturnUrl=" . $backlink : "");
    }

    /**
     * vrací url na odhlášení
     * @return url
     */
    public function getLogoutUrl() {
        return ($this->isTestMode ? "http://test-is" : "https://is") . ".skaut.cz/Login/LogOut.aspx?appid=" . $this->getAppId() . "&token=".$this->getToken() ;
    }

    /**
     * kontoluje jestli je přihlášení platné
     * @return bool 
     */
    public function isLoggedIn() {
        if ($this->getToken() != NULL && $this->user->UserDetail()->ID != NULL)
            return TRUE;
        return FALSE;
    }

    /**
     * vrací informace o přihlášené osobě
     * @return stdClass 
     */
    public function getMyDetail() {
        return $this->user->userDetail();
    }

    /**
     * prodloužení přihlášení
     * @param int $time
     * @return int - čas nového odhlášení
     */
    function updateLogoutTime($time = 1800) {
        $this->user->LoginUpdateRefresh(array("ID" => $this->getToken()));
        //return $this->perStorage->data['logoutTime'] += $time;
    }

    /**
     * list of WSDL
     */
    public function getWsdlList() {
        $ret = array();
        foreach ($this->wsdl as $key => $value) {
            $ret[$key] = $key;
        }
        return $ret;
    }

}