<?php

namespace Skautis;

use Skautis\SessionAdapter\AdapterInterface;
use Skautis\Wsdl\WsdlManager;

/**
 * @author Petr Morávek <petr@pada.cz>
 */
class User
{

    const ID_LOGIN = "ID_Login";
    const ID_ROLE = "ID_Role";
    const ID_UNIT = "ID_Unit";
    const LOGOUT_DATE = "LOGOUT_Date";
    const AUTH_CONFIRMED = "AUTH_Confirmed";
    const SESSION_ID = "skautis_user_data";

    /**
     * @var WsdlManager
     */
    private $wsdlManager;

    /**
     * @var AdapterInterface
     */
    private $session;

    /**
     * Informace o přihlášení uživatele
     *
     * @var array
     */
    protected $loginData = [];


    /**
     * @param WsdlManager $wsdlManager
     * @param AdapterInterface|null $session
     */
    public function __construct(WsdlManager $wsdlManager, AdapterInterface $session = null)
    {
        $this->wsdlManager = $wsdlManager;
        $this->session = $session;

        if ($session !== null && $session->has(self::SESSION_ID)) {
            $this->loginData = (array)$session->get(self::SESSION_ID);
        }
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
     * @param string|null $loginId
     * @param int|null $roleId
     * @param int|null $unitId
     * @param \DateTime|null $logoutDate
     * @return self
     */
    public function setLoginData($loginId = null, $roleId = null, $unitId = null, \DateTime $logoutDate = null)
    {
        $this->loginData = [];

        return $this->updateLoginData($loginId, $roleId, $unitId, $logoutDate);
    }
    
    /**
     * Hromadná změna údajů, bez vymazání stávajících
     *
     * @param string|null $loginId
     * @param int|null $roleId
     * @param int|null $unitId
     * @param \DateTime|null $logoutDate
     * @return self
     */
    public function updateLoginData($loginId = null, $roleId = null, $unitId = null, \DateTime $logoutDate = null)
    {
        if ($loginId !== null) {
            $this->loginData[self::ID_LOGIN] = $loginId;
        }

        if ($roleId !== null) {
            $this->loginData[self::ID_ROLE] = (int) $roleId;
        }

        if ($unitId !== null) {
            $this->loginData[self::ID_UNIT] = (int) $unitId;
        }

        if ($logoutDate !== null) {
            $this->loginData[self::LOGOUT_DATE] = $logoutDate;
        }

        $this->saveToSession();

        return $this;
    }

    /**
     * Hromadný reset dat po odhlášení
     *
     * @return self
     */
    public function resetLoginData()
    {
        return $this->setLoginData();
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
        $this->saveToSession();
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
     * @return self
     * @throws UnexpectedValueException pokud se nepodaří naparsovat datum
     */
    public function updateLogoutTime()
    {
        $loginId = $this->getLoginId();
        if ($loginId === null) {
            // Nemáme token, uživatel není přihlášen a není, co prodlužovat
            return $this;
        }

        $result = $this->wsdlManager->getWebService('UserManagement', $loginId)->LoginUpdateRefresh(["ID" => $loginId]);

        $logoutDate = preg_replace('/\.(\d*)$/', '', $result->DateLogout); //skautIS vrací sekundy včetně desetinné části
        $tz = new \DateTimeZone('Europe/Prague');
        $logoutDate = \DateTime::createFromFormat('Y-m-d\TH:i:s', $logoutDate, $tz);
        if ($logoutDate === false) {
            throw new UnexpectedValueException("Could not parse logout date '{$result->DateLogout}'.");
        }
        $this->loginData[self::LOGOUT_DATE] = $logoutDate;

        $this->saveToSession();

        return $this;
    }

    /**
     * Uloží nastavení do session
     *
     * @return void
     */
    protected function saveToSession()
    {
        if ($this->session !== null) {
            $this->session->set(self::SESSION_ID, $this->loginData);
        }
    }
}
