<?php
declare(strict_types = 1);

namespace Skaut\Skautis\Wsdl;

use Skaut\Skautis\InvalidArgumentException;

/**
 * @inheritdoc
 */
class WebServiceFactory implements WebServiceFactoryInterface
{

    /** @var string Třída webové služby */
    protected $class;


    public function __construct(string $className = WebService::class)
    {
       if (!is_a($className, WebServiceInterface::class, true)) {
         throw new InvalidArgumentException("Argument must be class name of a class implementing WebServiceInterface. '$className' given");
       }

        $this->class = $className;
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
        return new $this->class($soapClient, $options);
    }
}
