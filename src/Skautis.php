<?php

declare(strict_types=1);

namespace Skaut\Skautis;

use Skaut\Skautis\Wsdl\WebServiceAlias;
use Skaut\Skautis\Wsdl\WebServiceAliasNotFoundException;
use Skaut\Skautis\Wsdl\WebServiceInterface;
use Skaut\Skautis\Wsdl\WebServiceName;
use Skaut\Skautis\Wsdl\WebServiceNotFoundException;
use Skaut\Skautis\Wsdl\WsdlException;
use Skaut\Skautis\Wsdl\WsdlManager;

/**
 * Entry point of the library: web services, the logged-in user and the configuration.
 *
 * @author Hána František <sinacek@gmail.com>
 *
 * @property WebServiceInterface $ApplicationManagement
 * @property WebServiceInterface $ContentManagement
 * @property WebServiceInterface $DocumentStorage
 * @property WebServiceInterface $Evaluation
 * @property WebServiceInterface $Events
 * @property WebServiceInterface $Exports
 * @property WebServiceInterface $GoogleApps
 * @property WebServiceInterface $Grants
 * @property WebServiceInterface $Insurance
 * @property WebServiceInterface $Journal
 * @property WebServiceInterface $Material
 * @property WebServiceInterface $Message
 * @property WebServiceInterface $OrganizationUnit
 * @property WebServiceInterface $Power
 * @property WebServiceInterface $Reports
 * @property WebServiceInterface $Summary
 * @property WebServiceInterface $Task
 * @property WebServiceInterface $Telephony
 * @property WebServiceInterface $UserManagement
 * @property WebServiceInterface $Vivant
 * @property WebServiceInterface $Welcome
 */
class Skautis
{
    use HelperTrait;

    public function __construct(
        private readonly WsdlManager $wsdlManager,
        private readonly User $user,
    ) {
    }

    public function getWsdlManager(): WsdlManager
    {
        return $this->wsdlManager;
    }

    public function getConfig(): Config
    {
        return $this->wsdlManager->getConfig();
    }

    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param string $name full name of the web service or its alias
     *
     * @throws WsdlException
     */
    public function getWebService(string $name): WebServiceInterface
    {
        return $this->wsdlManager->getWebService($this->getWebServiceName($name), $this->user->getLoginId());
    }

    /**
     * Shortcut: $skautis->OrganizationUnit or $skautis->org.
     */
    public function __get(string $name): WebServiceInterface
    {
        return $this->getWebService($name);
    }

    /**
     * Web services are read-only; dynamic properties would silently shadow them.
     */
    public function __set(string $name, mixed $value): void
    {
        throw new DynamicPropertiesDisabledException();
    }

    public function getLoginUrl(string $backlink = ''): string
    {
        return $this->getConfig()->getBaseUrl().'Login/?'.$this->buildQuery($backlink);
    }

    public function getLogoutUrl(): string
    {
        $query = [
            'appid' => $this->getConfig()->getAppId(),
            'token' => $this->user->getLoginId(),
        ];

        return $this->getConfig()->getBaseUrl().'Login/LogOut.aspx?'.http_build_query($query, '', '&');
    }

    public function getRegisterUrl(string $backlink = ''): string
    {
        return $this->getConfig()->getBaseUrl().'Login/Registration.aspx?'.$this->buildQuery($backlink);
    }

    /**
     * Stores the data skautIS posts back after login.
     *
     * @param array<string, mixed> $data usually $_POST
     *
     * @throws UnexpectedValueException
     */
    public function setLoginData(array $data): void
    {
        $data = Helpers::parseLoginData($data);
        if ($data[User::ID_LOGIN] === null || $data[User::ID_LOGIN] === '') {
            throw new UnexpectedValueException('Login data do not contain skautIS_Token.');
        }

        $this->user->setLoginData($data[User::ID_LOGIN], $data[User::ID_ROLE], $data[User::ID_UNIT], $data[User::LOGOUT_DATE]);
    }

    /**
     * @throws Wsdl\MaintenanceErrorException
     */
    public function isMaintenance(): bool
    {
        return $this->wsdlManager->isMaintenance();
    }

    /**
     * @param string $name full name or alias of a web service
     *
     * @throws WebServiceNotFoundException
     */
    protected function getWebServiceName(string $name): string
    {
        if (WebServiceName::isValidServiceName($name)) {
            return $name;
        }

        try {
            return WebServiceAlias::resolveAlias($name);
        } catch (WebServiceAliasNotFoundException $exception) {
            throw new WebServiceNotFoundException($name, 0, $exception);
        }
    }

    private function buildQuery(string $backlink): string
    {
        $query = ['appid' => $this->getConfig()->getAppId()];
        if ($backlink !== '') {
            $query['ReturnUrl'] = $backlink;
        }

        return http_build_query($query, '', '&');
    }
}
