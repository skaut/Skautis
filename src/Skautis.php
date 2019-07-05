<?php
declare(strict_types = 1);

namespace Skautis;

use Skautis\Wsdl\WebService;
use Skautis\Wsdl\WebServiceAlias;
use Skautis\Wsdl\WebServiceAliasNotFoundException;
use Skautis\Wsdl\WebServiceInterface;
use Skautis\Wsdl\WebServiceName;
use Skautis\Wsdl\WebServiceNotFoundException;
use Skautis\Wsdl\WsdlException;
use Skautis\Wsdl\WsdlManager;

/**
 * Třída pro práci se skautISem
 *
 * Sdružuje všechny komponenty a zprostředkovává jejich komunikaci.
 *
 * @author Hána František <sinacek@gmail.com>
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
     * Zaznamy o provedenych dotazech na Skautis
     * Pokud je null, query logging je vypnuto
     *
     * @var SkautisQuery[]|null
     */
    private $log;


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
     * Zapne logování všech SOAP callů
     */
    public function enableDebugLog(): void
    {
        if ($this->log !== null) {
            // Debug log byl již zapnut dříve.
            return;
        }

        $this->log = [];
        $logger = function (SkautisQuery $query) {
            $this->log[] = $query;
        };
        $this->wsdlManager->addWebServiceListener(WebService::EVENT_SUCCESS, $logger);
        $this->wsdlManager->addWebServiceListener(WebService::EVENT_FAILURE, $logger);
    }

    /**
     * Vrací zalogované SOAP cally
     */
    public function getDebugLog(): array
    {
        return $this->log ?? [];
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
