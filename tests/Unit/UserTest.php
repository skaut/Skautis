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
        $data = array(
            'skautIS_Token' => "token",
            'skautIS_IDRole' => 33,
            'skautIS_IDUnit' => 100,
            'skautIS_DateLogout' => '2. 12. 2014 23:56:02'
        );

        $user = $this->makeUser();
        $this->assertFalse($user->isLoggedIn());

        $user->setLoginData($data);
        $this->assertEquals("token", $user->getLoginId());
        $this->assertEquals(33, $user->getRoleId());
        $this->assertEquals(100, $user->getUnitId());
        $this->assertEquals("2014-12-02 23:56:02", $user->getLogoutDate()->format('Y-m-d H:i:s'));
    }


    public function testIsLoggedHardCheck()
    {
        $soapResponse = new \StdClass();
        $soapResponse->DateLogout = '2044-02-12T15:19:21.996';

        $ws = \Mockery::mock("\Skautis\Wsdl\WebService");
        $ws->shouldReceive("LoginUpdateRefresh")->once()->andReturn($soapResponse);

        $wsdlManager = $this->makeWsdlManager();
        $wsdlManager->shouldReceive('getWebService')->once()->andReturn($ws);

        $data = array(
            'skautIS_Token' => "token",
            'skautIS_IDRole' => 33,
            'skautIS_IDUnit' => 100,
            'skautIS_DateLogout' => '2. 12. 2099 23:56:02'
        );

        $user = new User($wsdlManager);
        $user->setLoginData($data);

        $this->assertTrue($user->isLoggedIn(true));
    }

    public function testResetLoginData()
    {
        $data = array(
            'skautIS_Token' => "token",
            'skautIS_IDRole' => 33,
            'skautIS_IDUnit' => 100,
            'skautIS_DateLogout' => '2. 12. 2014 23:56:02'
        );

        $user = $this->makeUser();

        $user->setLoginData($data);
        $this->assertEquals(33, $user->getRoleId());

        $user->resetLoginData();
        $this->assertEmpty($user->getLoginId());
        $this->assertEmpty($user->getRoleId());
        $this->assertEmpty($user->getUnitId());
        $this->assertNull($user->getLogoutDate());
    }

}
