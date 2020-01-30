<?php

namespace Skaut\Skautis\Test\Unit;

use PHPUnit\Framework\TestCase;
use Skaut\Skautis\Config;
use Skaut\Skautis\DynamicPropertiesDisabledException;
use Skaut\Skautis\Skautis;
use Skaut\Skautis\User;
use Skaut\Skautis\Wsdl\WebServiceName;
use Skaut\Skautis\Wsdl\WsdlManager;

class SkautisTest extends TestCase
{

    protected function tearDown(): void
    {
      \Mockery::close();
    }

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

    public function testSettingWebService(): void {
      $skautis = Skautis::getInstance('asd');

      $this->expectException(DynamicPropertiesDisabledException::class);
      $skautis->UserManagement = 'asd';
    }

    public function testGetLoginURL(): void {
      $skautis = Skautis::getInstance('asd');
      $urlEncodedAddress = 'https://is.skaut.cz/Login/?appid=asd&ReturnUrl=https%3A%2F%2Fmy-web.nowhere%2Fasd';
      $this->assertEquals($urlEncodedAddress, $skautis->getLoginUrl('https://my-web.nowhere/asd'));
    }


    public function testGetLogoutURL(): void {

      /** @var User $user */
      $user = \Mockery::mock(User::class);
      $user->shouldReceive('getLoginId')
        ->once()
        ->andReturn('log://123out');

      /** @var WsdlManager $wsdlManager */
      $config = new Config('asd');
      $wsdlManager = \Mockery::mock(WsdlManager::class);
      $wsdlManager->shouldReceive('getConfig')
        ->andReturn($config);

      $skautis = new Skautis($wsdlManager, $user);

      $urlEncodedAddress = 'https://test-is.skaut.cz/Login/LogOut.aspx?appid=asd&token=log%3A%2F%2F123out';
      $this->assertEquals($urlEncodedAddress, $skautis->getLogoutUrl());
    }

    public function testGetRegisterURL(): void {
      $skautis = Skautis::getInstance('asd');
      $urlEncodedAddress = 'https://is.skaut.cz/Login/Registration.aspx?appid=asd';
      $this->assertEquals($urlEncodedAddress, $skautis->getRegisterUrl());
    }

    public function testEventSetter(): void
    {
      $this->markTestSkipped();
        //@TODO
    }
}
