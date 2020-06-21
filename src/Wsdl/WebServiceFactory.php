<?php
declare(strict_types = 1);

namespace Skaut\Skautis\Wsdl;

use Psr\EventDispatcher\EventDispatcherInterface;
use Skaut\Skautis\InvalidArgumentException;

final class WebServiceFactory implements WebServiceFactoryInterface
{

    /**
     * @var string Třída webové služby
     */
    private $class;

    /**
     * @var EventDispatcherInterface|null
     */
    private $eventDispatcher;


    /**
     * @param string $className Constructor must accept SoapClient, SOAP options array and EventDispatcherInterface
     */
    public function __construct(
      string $className = WebService::class,
      ?EventDispatcherInterface $eventDispatcher = null
    ) {
        if (!is_a($className, WebServiceInterface::class, true)) {
          throw new InvalidArgumentException("Argument must be class name of a class implementing WebServiceInterface. '$className' given");
        }

        $this->class = $className;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @inheritdoc
     */
    public function createWebService(string $url, array $options): WebServiceInterface
    {
        if (empty($url)) {
          throw new InvalidArgumentException('WSDL URL cannot be empty.');
        }

        $soapClient = new \SoapClient($url, $options);
        return new $this->class($soapClient, $options, $this->eventDispatcher);
    }
}
