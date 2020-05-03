<?php

namespace Skaut\Skautis\Test\Unit;

use PHPUnit\Framework\TestCase;
use Skaut\Skautis\User;
use Skaut\Skautis\Wsdl\WebService;
use Skaut\Skautis\Wsdl\WsdlManager;

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
        $dt = new \DateTimeImmutable;
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
        $user->setLoginData('token', 33, 100, new \DateTimeImmutable('+1 day'));

        $this->assertTrue($user->isLoggedIn(true));
    }

    public function testResetLoginData(): void
    {
        $user = $this->makeUser();

        $user->setLoginData('token', 33, 100, new \DateTimeImmutable);
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
