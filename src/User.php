<?php

declare(strict_types=1);

namespace Skaut\Skautis;

use DateTimeImmutable;
use DateTimeZone;
use Skaut\Skautis\SessionAdapter\AdapterInterface;
use Skaut\Skautis\Wsdl\AuthenticationException;
use Skaut\Skautis\Wsdl\WebServiceName;
use Skaut\Skautis\Wsdl\WsdlManager;
use stdClass;
use Throwable;

/**
 * Login data of the current user, kept in the session adapter.
 *
 * @author Petr Morávek <petr@pada.cz>
 */
class User
{
    public const string ID_LOGIN = 'ID_Login';
    public const string ID_ROLE = 'ID_Role';
    public const string ID_UNIT = 'ID_Unit';
    public const string LOGOUT_DATE = 'LOGOUT_Date';

    private const string AUTH_CONFIRMED = 'AUTH_Confirmed';
    private const string SESSION_ID = 'skautis_user_data';

    /** @var array<string, mixed> */
    protected array $loginData = [];

    public function __construct(
        private readonly WsdlManager $wsdlManager,
        private readonly ?AdapterInterface $session = null,
    ) {
        $stored = $session?->get(self::SESSION_ID);
        if (\is_array($stored)) {
            foreach ($stored as $key => $value) {
                if (\is_string($key)) {
                    $this->loginData[$key] = $value;
                }
            }
        }
    }

    public function getLoginId(): ?string
    {
        $loginId = $this->loginData[self::ID_LOGIN] ?? null;

        return \is_string($loginId) ? $loginId : null;
    }

    public function getRoleId(): ?int
    {
        $roleId = $this->loginData[self::ID_ROLE] ?? null;

        return \is_int($roleId) ? $roleId : null;
    }

    public function getUnitId(): ?int
    {
        $unitId = $this->loginData[self::ID_UNIT] ?? null;

        return \is_int($unitId) ? $unitId : null;
    }

    /**
     * When skautIS logs the user out automatically.
     */
    public function getLogoutDate(): ?DateTimeImmutable
    {
        $logoutDate = $this->loginData[self::LOGOUT_DATE] ?? null;

        return $logoutDate instanceof DateTimeImmutable ? $logoutDate : null;
    }

    /**
     * Replaces all login data, typically right after skautIS posts the login back.
     */
    public function setLoginData(
        string $loginId,
        ?int $roleId = null,
        ?int $unitId = null,
        ?DateTimeImmutable $logoutDate = null,
    ): static {
        $this->loginData = [];

        return $this->updateLoginData($loginId, $roleId, $unitId, $logoutDate);
    }

    /**
     * Changes the given values and keeps the rest.
     */
    public function updateLoginData(
        ?string $loginId = null,
        ?int $roleId = null,
        ?int $unitId = null,
        ?DateTimeImmutable $logoutDate = null,
    ): static {
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

    public function resetLoginData(): static
    {
        $this->loginData = [];
        $this->saveToSession();

        return $this;
    }

    /**
     * Whether the login is still valid. The server clock must be right for this to work.
     *
     * @param bool $hardCheck ask skautIS even when the login was confirmed before
     *
     * @throws Throwable when skautIS rejects the login
     */
    public function isLoggedIn(bool $hardCheck = false): bool
    {
        $loginId = $this->getLoginId();
        if ($loginId === null || $loginId === '') {
            return false;
        }

        if ($hardCheck || ! $this->isAuthConfirmed()) {
            $this->confirmAuth();
        }

        $logoutDate = $this->getLogoutDate();
        if ($logoutDate === null) {
            return false;
        }

        return $this->isAuthConfirmed() && $logoutDate->getTimestamp() > time();
    }

    /**
     * Extends the login by 30 minutes.
     *
     * @return bool false when there is no login to extend
     *
     * @throws UnexpectedValueException when skautIS returns an unparsable date
     */
    public function updateLogoutTime(): bool
    {
        $loginId = $this->getLoginId();
        if ($loginId === null) {
            return false;
        }

        $result = $this->wsdlManager
            ->getWebService(WebServiceName::USER_MANAGEMENT, $loginId)
            ->call('LoginUpdateRefresh', [['ID' => $loginId]]);

        if (! $result instanceof stdClass || ! isset($result->DateLogout) || ! \is_string($result->DateLogout)) {
            throw new UnexpectedValueException('LoginUpdateRefresh did not return DateLogout.');
        }

        $this->loginData[self::LOGOUT_DATE] = $this->parseDate($result->DateLogout);
        $this->saveToSession();

        return true;
    }

    protected function isAuthConfirmed(): bool
    {
        return ($this->loginData[self::AUTH_CONFIRMED] ?? false) === true;
    }

    protected function setAuthConfirmed(bool $isConfirmed): void
    {
        $this->loginData[self::AUTH_CONFIRMED] = $isConfirmed;
        $this->saveToSession();
    }

    /**
     * Confirms (and extends) the login by asking skautIS.
     *
     * @throws Throwable when skautIS rejects the login
     */
    protected function confirmAuth(): void
    {
        try {
            if (! $this->updateLogoutTime()) {
                throw new AuthenticationException('There is no login to confirm.');
            }
            $this->setAuthConfirmed(true);
        } catch (Throwable $exception) {
            $this->setAuthConfirmed(false);
            throw $exception;
        }
    }

    protected function saveToSession(): void
    {
        $this->session?->set(self::SESSION_ID, $this->loginData);
    }

    /**
     * @throws UnexpectedValueException
     */
    private function parseDate(string $dateText): DateTimeImmutable
    {
        // skautIS returns seconds with a fractional part, e.g. 2044-02-12T15:19:21.996
        $withoutFraction = preg_replace('/\.\d*$/', '', $dateText);
        if ($withoutFraction === null) {
            throw new UnexpectedValueException("Could not parse date '$dateText'.");
        }

        $dateTime = DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s', $withoutFraction, new DateTimeZone('Europe/Prague'));
        if ($dateTime === false) {
            throw new UnexpectedValueException("Could not parse date '$dateText'.");
        }

        return $dateTime;
    }
}
