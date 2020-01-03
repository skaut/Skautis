<?php

namespace Test\Skautis;

use PHPUnit\Framework\TestCase;
use Skautis\User;
use Skautis\Wsdl\WsdlManager;
use Skautis\Wsdl\WebService;

class UserTest extends TestCase
{

    protected function tearDown(): void
    {
        \Mockery::close();
    }

    protected function makeWsdlManager()
    {
        return \Mockery::mock(WsdlManager::class);
    }

    protected function makeUser(): User
    {
        return new User($this->makeWsdlManager());
    }

    public function testSetLoginData(): void
    {
        $dt = new \DateTime;
        $user = $this->makeUser();
        $this->assertFalse($user->isLoggedIn());

        $user->setLoginData('token', 33, 100, $dt);
        $this->assertEquals('token', $user->getLoginId());
        $this->assertEquals(33, $user->getRoleId());
        $this->assertEquals(100, $user->getUnitId());
        $this->assertEquals($dt->format('Y-m-d H:i:s'), $user->getLogoutDate()->format('Y-m-d H:i:s'));
    }


    public function testIsLoggedHardCheck(): void
    {
        $soapResponse = new \StdClass();
        $soapResponse->DateLogout = '2044-02-12T15:19:21.996';

        $ws = \Mockery::mock(WebService::class);
        $ws->shouldReceive('LoginUpdateRefresh')->once()->andReturn($soapResponse);

        $wsdlManager = $this->makeWsdlManager();
        $wsdlManager->shouldReceive('getWebService')->once()->andReturn($ws);

        $user = new User($wsdlManager);
        $user->setLoginData('token', 33, 100, new \DateTime('+1 day'));

        $this->assertTrue($user->isLoggedIn(true));
    }

    public function testResetLoginData(): void
    {
        $user = $this->makeUser();

        $user->setLoginData('token', 33, 100, new \DateTime);
        $this->assertEquals(33, $user->getRoleId());

        $user->resetLoginData();
        $this->assertEmpty($user->getLoginId());
        $this->assertEmpty($user->getRoleId());
        $this->assertEmpty($user->getUnitId());
        $this->assertNull($user->getLogoutDate());
    }

    public function testIsLoggedInWithoutUserId(): void
    {
      $user = $this->makeUser();
      $this->assertNull($user->getLoginId());
      $this->assertFalse($user->isLoggedIn());
      $this->assertFalse($user->isLoggedIn(true));
    }

}
