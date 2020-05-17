<?php
declare(strict_types = 1);

namespace Skaut\Skautis;

use Skaut\Skautis\Wsdl\WebServiceAlias;
use Skaut\Skautis\Wsdl\WebServiceAliasNotFoundException;
use Skaut\Skautis\Wsdl\WebServiceInterface;
use Skaut\Skautis\Wsdl\WebServiceName;
use Skaut\Skautis\Wsdl\WebServiceNotFoundException;
use Skaut\Skautis\Wsdl\WsdlException;
use Skaut\Skautis\Wsdl\WsdlManager;

/**
 * Třída pro práci se skautISem
 *
 * Sdružuje všechny komponenty a zprostředkovává jejich komunikaci.
 *
 * @author Hána František <sinacek@gmail.com>
 *
 * @property-read WebServiceInterface $ApplicationManagement
 * @property-read WebServiceInterface $ContentManagement
 * @property-read WebServiceInterface $Evaluation
 * @property-read WebServiceInterface $Events
 * @property-read WebServiceInterface $Exports
 * @property-read WebServiceInterface $GoogleApps
 * @property-read WebServiceInterface $Journal
 * @property-read WebServiceInterface $Material
 * @property-read WebServiceInterface $Message
 * @property-read WebServiceInterface $OrganizationUnit
 * @property-read WebServiceInterface $Power
 * @property-read WebServiceInterface $Reports
 * @property-read WebServiceInterface $Summary
 * @property-read WebServiceInterface $Task
 * @property-read WebServiceInterface $Telephony
 * @property-read WebServiceInterface $UserManagement
 * @property-read WebServiceInterface $Vivant
 * @property-read WebServiceInterface $Welcome
 */
class Skautis
{

    use HelperTrait;

    /**
     * @var WsdlManager
     */
    private $wsdlManager;

    /**
     * @var User
     */
    private $user;

    /**
     * @param WsdlManager $wsdlManager
     * @param User $user
     */
    public function __construct(WsdlManager $wsdlManager, User $user)
    {
        $this->wsdlManager = $wsdlManager;
        $this->user = $user;
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
     * Získá objekt webové služby
     */
    public function getWebService(string $name): WebServiceInterface
    {
        $realServiceName = $this->getWebServiceName($name);
        return $this->wsdlManager->getWebService($realServiceName, $this->user->getLoginId());
    }

    /**
     * Trocha magie pro snadnější přístup k webovým službám.
     */
    public function __get(string $name): WebServiceInterface
    {
        return $this->getWebService($name);
    }

  /**
   * NEPOUŽÍVAT - vždy vyhodí výjimku
   *
   * @deprecated
   * @param string $name
   * @param mixed $value
   *
   * @return void
   *
   * @phpstan-return never
   */
    public function __set(
      $name,
      $value
    ) {
      throw new DynamicPropertiesDisabledException();
    }


  /**
     * Vrací URL na přihlášení
     */
    public function getLoginUrl(string $backlink = ''): string
    {
        $query = [];
        $query['appid'] = $this->getConfig()->getAppId();
        if (!empty($backlink)) {
            $query['ReturnUrl'] = $backlink;
        }
        return $this->getConfig()->getBaseUrl() . 'Login/?' . http_build_query($query, '', '&');
    }

    /**
     * Vrací URL na odhlášení
     */
    public function getLogoutUrl(): string
    {
        $query = [];
        $query['appid'] = $this->getConfig()->getAppId();
        $query['token'] = $this->user->getLoginId();
        return $this->getConfig()->getBaseUrl() . 'Login/LogOut.aspx?' . http_build_query($query, '', '&');
    }

    /**
     * Vrací URL k registraci
     */
    public function getRegisterUrl(string $backlink = ''): string
    {
        $query = [];
        $query['appid'] = $this->getConfig()->getAppId();
        if (!empty($backlink)) {
            $query['ReturnUrl'] = $backlink;
        }
        return $this->getConfig()->getBaseUrl() . 'Login/Registration.aspx?' . http_build_query($query, '', '&');
    }

    /**
     * Hromadné nastavení po přihlášení
     *
     * @param array<string, mixed> $data
     */
    public function setLoginData(array $data): void
    {
        $data = Helpers::parseLoginData($data);
        $this->getUser()->setLoginData($data[User::ID_LOGIN], $data[User::ID_ROLE], $data[User::ID_UNIT], $data[User::LOGOUT_DATE]);
    }

    /**
     * Ověřuje, zda je skautIS odstaven pro údržbu
     */
    public function isMaintenance(): bool
    {
        return $this->wsdlManager->isMaintenance();
    }

  /**
   * Vrací celé jméno webové služby
   *
   * @param string $name jméno nebo alias webové služby
   *
   * @throws WsdlException
   */
  protected function getWebServiceName(string $name): string
  {
    if (WebServiceName::isValidServiceName($name)) {
      return $name;
    }

    try {
      return WebServiceAlias::resolveAlias($name);
    }
    catch (WebServiceAliasNotFoundException $ex) {
      throw new WebServiceNotFoundException($name, 0, $ex);
    }
  }
}
