<?php

namespace Test\Skautis;

use Skautis\Wsdl\WebServiceFactory;
use Skautis\Wsdl\WebServiceInterface;

class WebServiceFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testCallback()
    {
        $factory = new WebServiceFactory('Test\Skautis\WebServiceStub');
        $args = ['cache' => false];
        $webService = $factory->createWebService("http://moje-wsdl.xml", $args);

        $this->assertEquals("http://moje-wsdl.xml", $webService->getWsdl());
        $this->assertEquals($args, $webService->getSoapArgs());
    }
}


class WebServiceStub implements  WebServiceInterface
{

    protected $wsdl;

    protected $soapArgs;

    public function __construct($wsdl, $soapArgs)
    {
        $this->wsdl = $wsdl;
        $this->soapArgs = $soapArgs;
    }

    public function getWsdl()
    {
        return $this->wsdl;
    }

    public function getSoapArgs()
    {
        return $this->soapArgs;
    }

  /**
   * Přidá listener na událost.
   */
  public function subscribe(
    string $eventName,
    callable $callback
  ): void {
    // Empty
  }

  /**
   * Zavola funkci na Skautisu
   *
   * @param string $functionName Jmeno funkce volane na skautisu
   * @param array $arguments Argumenty funkce volane na skautisu
   *
   * @return mixed
   */
  public function call(
    string $functionName,
    array $arguments = []
  ) {
    return null;
  }

  /**
   * Zavola funkci na Skautisu
   *
   * @param string $functionName Jmeno funkce volane na skautisu
   * @param array $arguments Argumenty funkce volane na skautisu
   *
   * @return mixed
   */
  public function __call(
    string $functionName,
    array $arguments
  ) {
    return null;
  }
}
