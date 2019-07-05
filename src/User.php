<?php
declare(strict_types = 1);

namespace Skautis;

use DateTime;
use Skautis\SessionAdapter\AdapterInterface;
use Skautis\Wsdl\WebServiceName;
use Skautis\Wsdl\WsdlManager;

/**
 * @author Petr Morávek <petr@pada.cz>
 */
class User
{

    public const ID_LOGIN = 'ID_Login';
    public const ID_ROLE = 'ID_Role';
    public const ID_UNIT = 'ID_Unit';
    public const LOGOUT_DATE = 'LOGOUT_Date';
    private const AUTH_CONFIRMED = 'AUTH_Confirmed';
    private const SESSION_ID = 'skautis_user_data';

    /**
     * @var WsdlManager
     */
    private $wsdlManager;

    /**
     * @var AdapterInterface|null
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

    public function getLoginId(): ?string
    {
        return $this->loginData[self::ID_LOGIN] ?? null;
    }

    public function getRoleId(): ?int
    {
        return $this->loginData[self::ID_ROLE] ?? null;
    }

    public function getUnitId(): ?int
    {
        return $this->loginData[self::ID_UNIT] ?? null;
    }

    /**
     * Vrací datum a čas automatického odhlášení ze skautISu
     */
    public function getLogoutDate(): ?DateTime
    {
        return $this->loginData[self::LOGOUT_DATE] ?? null;
    }

    /**+
     * Hromadné nastavení po přihlášení
     */
    public function setLoginData(
      ?string $loginId = null,
      ?int $roleId = null,
      ?int $unitId = null,
      ?DateTime $logoutDate = null
    ): self {
        $this->loginData = [];

        return $this->updateLoginData($loginId, $roleId, $unitId, $logoutDate);
    }
    
    /**
     * Hromadná změna údajů, bez vymazání stávajících
     */
    public function updateLoginData(
      ?string $loginId = null,
      ?int $roleId = null,
      ?int $unitId = null,
      ?DateTime $logoutDate = null
    ): self {
        if ($loginId !== null) {
            $this->loginData[self::ID_LOGIN] = $loginId;
        }

        if ($roleId !== null) {
            $this->loginData[self::ID_ROLE] = $roleId;
        }

        if ($unitId !== null) {
            $this->loginData[self::ID_UNIT] = $unitId;
        }

        if ($logoutDate !== null) {
            $this->loginData[self::LOGOUT_DATE] = $logoutDate;
        }

        $this->saveToSession();

        return $this;
    }

    /**
     * Hromadný reset dat po odhlášení
     */
    public function resetLoginData(): self
    {
        return $this->setLoginData();
    }

    /**
     * Kontoluje, jestli je přihlášení platné.
     * Pro správné fungování je nezbytně nutné, aby byl na serveru nastaven správný čas.
     *
     * @param bool $hardCheck vynutí kontrolu přihlášení na serveru
     */
    public function isLoggedIn(bool $hardCheck = false): bool
    {
        if (empty($this->loginData[self::ID_LOGIN])) {
            return false;
        }

        if ($hardCheck || !$this->isAuthConfirmed()) {
            $this->confirmAuth();
        }

        if ($this->getLogoutDate() === null) {
          return false;
        }

        return $this->isAuthConfirmed() && $this->getLogoutDate()->getTimestamp() > time();
    }

    /**
     * Bylo potvrzeno přihlášení dotazem na skautIS?
     */
    protected function isAuthConfirmed(): bool
    {
        return !empty($this->loginData[self::AUTH_CONFIRMED]);
    }

    protected function setAuthConfirmed(bool $isConfirmed): void
    {
        $this->loginData[self::AUTH_CONFIRMED] = $isConfirmed;
        $this->saveToSession();
    }

    /**
     * Potvrdí (a prodlouží) přihlášení dotazem na skautIS.
     */
    protected function confirmAuth(): void
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
     * @throws UnexpectedValueException pokud se nepodaří naparsovat datum
     */
    public function updateLogoutTime(): self
    {
        $loginId = $this->getLoginId();
        if ($loginId === null) {
            // Nemáme token, uživatel není přihlášen a není, co prodlužovat
            return $this;
        }

        $result = $this->wsdlManager->getWebService(WebServiceName::USER_MANAGEMENT, $loginId)->LoginUpdateRefresh(['ID' => $loginId]);

        $logoutDate = preg_replace('/\.(\d*)$/', '', $result->DateLogout); //skautIS vrací sekundy včetně desetinné části
        $tz = new \DateTimeZone('Europe/Prague');
        $logoutDate = DateTime::createFromFormat('Y-m-d\TH:i:s', $logoutDate, $tz);
        if ($logoutDate === false) {
            throw new UnexpectedValueException("Could not parse logout date '{$result->DateLogout}'.");
        }
        $this->loginData[self::LOGOUT_DATE] = $logoutDate;

        $this->saveToSession();

        return $this;
    }

    /**
     * Uloží nastavení do session
     */
    protected function saveToSession(): void
    {
        if ($this->session !== null) {
            $this->session->set(self::SESSION_ID, $this->loginData);
        }
    }
}
