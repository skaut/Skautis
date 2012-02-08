<?php

/**
 * @author sinacek
 * Singleton
 */
class SkautIS_Mapper {
// <editor-fold defaultstate="collapsed" desc="vars">
    const APP_ID = "ID_Application";
    const TOKEN = "ID_Login";
    const ID_ROLE = "ID_Role";
    const ID_UNIT = "ID_Unit";

    /**
     * sigleton
     * @var SkautIS_Mapper 
     */
    private static $instance;

    /**
     * uchovává data pouze během jednoho požadavku
     * @var type 
     */
    private $temp;

    /**
     * seznam všech dostupných WSDL
     * @var array
     */
    private $wsdl = array(
        "user" => array("wsdl" => "UserManagement"),
        "org" => array("wsdl" => "OrganizationUnit"),
        "events" => array("wsdl" => "Events"),
        "app" => array("wsdl" => "ApplicationManagement"),
        "evaluation" => array("wsdl" => "Evaluation"),
        "exports" => array("wsdl" => "Exports"),
        "journal" => array("wsdl" => "Journal"),
        "message" => array("wsdl" => "Message"),
        "reports" => array("wsdl" => "Reports"),
        "summary" => array("wsdl" => "Summary"),
        "tel" => array("wsdl" => "Telephony"),
        "welcome" => array("wsdl" => "Welcome")
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
     * persistatnt array
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
    }
    
    /**
     * Singleton
     * @var bool $appId možnost rovnou nastavit appId
     * @return SkautIS_Mapper
     */
    public static function getInstance($appId = FALSE) {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self;
        }
        if($appId)
            self::$instance->setAppId($appId);
        return self::$instance;
    }

    public function __get($name) {
        if (!isset($this->perStorage->init[self::APP_ID])) {
            throw new SkautIS_AbortException("ID_Application is not set");
        }

        $name = strtolower($name);
        if (!array_key_exists($name, $this->wsdl)) {
            throw new SkautIS_WsdlException('Invalid WSDL: "' . $name . '"');
        }

        if (!isset($this->active[$name])) {
            $wsdl = ($this->isTestMode ? "http://test-is" : "https://is") . ".skaut.cz/JunakWebservice/" . $this->wsdl[$name]["wsdl"] . ".asmx?WSDL";
            $this->active[$name] = new SkautIS_WS($wsdl, $this->perStorage->init, $this->compression);
        }
        return $this->active[$name];
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
        throw new NotImplementedException();
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
     * @param bool $noStoradged
     * @return type 
     */
    public function getMyDetail($noStoradged = FALSE) {
        if (!isset($this->temp[__METHOD__]) || $noStoradged)
            return $this->temp[__METHOD__] = $this->user->userDetail();
        return $this->temp[__METHOD__];
    }

    /**
     * prodloužení přihlášení
     * @param int $time
     * @return int - čas nového odhlášení
     */
    function updateLogoutTime($time = 1800) {
        $this->user->LoginUpdateRefresh(array("ID" => $this->getToken()));
        return $this->perStorage->data['logoutTime'] += $time;
    }
    
    /**
     * list of WSDL
     */
    public function getWsdlList() {
        $ret = array();
        foreach ($this->wsdl as $key => $value) {
            $ret[$key] = $value["wsdl"];
        }
        return $ret;
    }


}