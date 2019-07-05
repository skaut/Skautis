<?php

namespace Test\Skautis;

use PHPUnit_Framework_TestCase;
use Skautis\Config;
use Skautis\Skautis;
use Skautis\Wsdl\WebService;
use Skautis\Wsdl\WebServiceInterface;
use Skautis\Wsdl\WebServiceName;

class SkautisTest extends PHPUnit_Framework_TestCase
{

    public function testSingletonSameId(): void
    {
        $skautis = Skautis::getInstance('asd');
        $skautisA = Skautis::getInstance('asd');
        $this->assertSame($skautis, $skautisA);
    }

    public function testSingletonDifferentId(): void
    {
        $skautis = Skautis::getInstance('asd');
        $skautisA = Skautis::getInstance('qwe');
        $this->assertNotSame($skautis, $skautisA);
    }

    public function testSingletonTestMode(): void
    {
        $appId = 'some-app-id';

        $skautisWithTestMode = Skautis::getInstance($appId, Config::TEST_MODE_ENABLED);
        $skautisWithoutTestMode = Skautis::getInstance($appId, Config::TEST_MODE_DISABLED);

        $this->assertNotSame($skautisWithTestMode, $skautisWithoutTestMode);
    }

    public function testGettingService(): void {
      $skautis = Skautis::getInstance('asd');

      $serviceA = $skautis->UserManagement;
      $serviceB = $skautis->getWebService(WebServiceName::USER_MANAGEMENT);

      $this->assertSame($serviceA, $serviceB);
    }

    public function testGettingServiceUsingAlias(): void {
      $skautis = Skautis::getInstance('asd');

      $serviceA = $skautis->UserManagement;
      $serviceB = $skautis->user;
      $serviceC = $skautis->usr;

      $this->assertSame($serviceA, $serviceB);
      $this->assertSame($serviceB, $serviceC);
    }

    public function testEventSetter(): void
    {
        //@TODO
    }
}
