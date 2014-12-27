<?php

namespace Test\Skautis;

use Skautis\User;


class UserTest extends \PHPUnit_Framework_TestCase
{

    protected function tearDown()
    {
        \Mockery::close();
    }

    protected function makeWsdlManager()
    {
        return \Mockery::mock('\Skautis\Wsdl\WsdlManager');
    }

    protected function makeUser()
    {
        return new User($this->makeWsdlManager());
    }

    public function testSetLoginData()
    {
        $dt = new \DateTime;
        $user = $this->makeUser();
        $this->assertFalse($user->isLoggedIn());

        $user->setLoginData("token", 33, 100, $dt);
        $this->assertEquals("token", $user->getLoginId());
        $this->assertEquals(33, $user->getRoleId());
        $this->assertEquals(100, $user->getUnitId());
        $this->assertEquals($dt->format('Y-m-d H:i:s'), $user->getLogoutDate()->format('Y-m-d H:i:s'));
    }


    public function testIsLoggedHardCheck()
    {
        $soapResponse = new \StdClass();
        $soapResponse->DateLogout = '2044-02-12T15:19:21.996';

        $ws = \Mockery::mock("\Skautis\Wsdl\WebService");
        $ws->shouldReceive("LoginUpdateRefresh")->once()->andReturn($soapResponse);

        $wsdlManager = $this->makeWsdlManager();
        $wsdlManager->shouldReceive('getWebService')->once()->andReturn($ws);

        $user = new User($wsdlManager);
        $user->setLoginData("token", 33, 100, new \DateTime("+1 day"));

        $this->assertTrue($user->isLoggedIn(true));
    }

    public function testResetLoginData()
    {
        $user = $this->makeUser();

        $user->setLoginData("token", 33, 100, new \DateTime);
        $this->assertEquals(33, $user->getRoleId());

        $user->resetLoginData();
        $this->assertEmpty($user->getLoginId());
        $this->assertEmpty($user->getRoleId());
        $this->assertEmpty($user->getUnitId());
        $this->assertNull($user->getLogoutDate());
    }

}
