<?php

declare(strict_types=1);

namespace Skaut\Skautis\Wsdl;

use Psr\EventDispatcher\EventDispatcherInterface;
use Skaut\Skautis\Config;
use Skaut\Skautis\User;

/**
 * Creates and keeps the web service objects.
 */
class WsdlManager
{
    /** @var array<string, WebServiceInterface> */
    protected array $webServices = [];

    public function __construct(
        protected readonly WebServiceFactoryInterface $webServiceFactory,
        protected readonly Config $config,
    ) {
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * @param string      $name    full name of the web service
     * @param string|null $loginId skautIS login token
     *
     * @throws WsdlException
     */
    public function getWebService(string $name, ?string $loginId = null): WebServiceInterface
    {
        $key = $loginId.'_'.$name;

        if (! isset($this->webServices[$key])) {
            $options = $this->config->getSoapOptions();
            $options[User::ID_LOGIN] = $loginId;
            $this->webServices[$key] = $this->createWebService($name, $options);
        }

        return $this->webServices[$key];
    }

    /**
     * @param array<string, mixed> $options SoapClient options plus ID_Application and ID_Login
     *
     * @throws WsdlException
     */
    public function createWebService(string $name, array $options = []): WebServiceInterface
    {
        return $this->webServiceFactory->createWebService($this->getWebServiceUrl($name), $options);
    }

    /**
     * The factory accepts one dispatcher only; combine listeners in your own dispatcher.
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->webServiceFactory->setEventDispatcher($eventDispatcher);
    }

    /**
     * Whether skautIS is down for maintenance (or unreachable).
     *
     * @throws MaintenanceErrorException on a network error such as a DNS failure
     */
    public function isMaintenance(): bool
    {
        // get_headers() reports network problems as PHP warnings; turn them into an exception
        set_error_handler(static function (int $errno, string $errstr, string $errfile, int $errline): never {
            throw new MaintenanceErrorException($errstr, $errno, $errfile, $errline);
        });

        try {
            $headers = get_headers($this->getWebServiceUrl(WebServiceName::USER_MANAGEMENT));
            if ($headers === false) {
                return true;
            }

            foreach ($headers as $header) {
                if (\is_string($header) && preg_match('~^HTTP/\S+\s+200\b~', $header) === 1) {
                    return false;
                }
            }

            return true;
        } finally {
            restore_error_handler();
        }
    }

    /**
     * @throws WsdlException
     */
    protected function getWebServiceUrl(string $name): string
    {
        if (! WebServiceName::isValidServiceName($name)) {
            throw new WebServiceNotFoundException($name);
        }

        return $this->config->getBaseUrl().'JunakWebservice/'.rawurlencode($name).'.asmx?WSDL';
    }
}
