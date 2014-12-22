<?php

namespace Test\Skautis;

use Skautis\Skautis;
use Skautis\Config;
use Skautis\SessionAdapter\SessionAdapter;
use Skautis\SessionAdapter\FakeAdapter;

class SkautisTest extends \PHPUnit_Framework_TestCase
{

    protected function tearDown()
    {
	\Mockery::close();
    }

    protected function makeSession()
    {
        return new FakeAdapter();
    }

    protected function makeWsdlManager()
    {
        $manager = \Mockery::mock("\Skautis\Wsdl\WsdlManager");
        $config = $this->makeConfig();
        $manager->shouldReceive('getConfig')->withNoArgs()->andReturn($config);
        return $manager;
    }

    protected function makeConfig()
    {
        $config =  new Config("asd123");
        $this->assertTrue($config->validate());

	return $config;
    }


    protected function makeSkautis()
    {
	return new Skautis($this->makeWsdlManager(), $this->makeSession());
    }

    public function testSetLoginData()
    {
        $skautIS = $this->makeSkautis();
	$data = array(
            'skautIS_Token' => "token",
            'skautIS_IDRole' => 33,
	    'skautIS_IDUnit' => 100,
	    'skautIS_DateLogout' => '2. 12. 2014 23:56:02'
	);
	$this->assertFalse($skautIS->isLoggedIn());

        $skautIS->setLoginData($data);
       // $this->assertEquals("token", $skautIS->getToken());
        $this->assertEquals(33, $skautIS->getRoleId());
	$this->assertEquals(100, $skautIS->getUnitId());

	$this->assertEquals("2014-12-02 23:56:02", $skautIS->getLogoutDate()->format('Y-m-d H:i:s'));
    }



    public function testIsLoggedHardCheck()
    {
	$soapResponse = new \StdClass();
	$soapResponse->DateLogout = '2044-02-12T15:19:21.996';

        $ws = \Mockery::mock("\Skautis\Wsdl\WebService");
        $ws->shouldReceive("LoginUpdateRefresh")->once()->andReturn($soapResponse);

	$wsdlManager = $this->makeWsdlManager();
	$wsdlManager->shouldReceive('getWebService')->once()->andReturn($ws);

	$config = $this->makeConfig();
	$session = $this->makeSession();

	$skautis = new Skautis($wsdlManager, $session);

	$data = array(
            'skautIS_Token' => "token",
            'skautIS_IDRole' => 33,
	    'skautIS_IDUnit' => 100,
	    'skautIS_DateLogout' => '2. 12. 2099 23:56:02'
        );
	$skautis->setLoginData($data);


        $this->assertTrue($skautis->isLoggedIn(true));
    }

    public function testResetLoginData()
    {
	$data = array(
            'skautIS_Token' => "token",
            'skautIS_IDRole' => 33,
	    'skautIS_IDUnit' => 100,
	    'skautIS_DateLogout' => '2. 12. 2014 23:56:02'
	);

	$skautis = $this->makeSkautis();

	$skautis->setLoginData($data);
	$this->assertEquals(33, $skautis->getRoleId());

	$skautis->resetLoginData();
//        $this->assertEmpty($skautis->getToken());
        $this->assertEmpty($skautis->getRoleId());
	$this->assertEmpty($skautis->getUnitId());
	$this->assertNull($skautis->getLogoutDate());
    }


    public function testFactorySingletonHibrid()
    {
        $skautis = Skautis::getInstance('789qwe');
	$this->assertInstanceOf('\Skautis\Skautis', $skautis);

	$skautisA = Skautis::getInstance('789qwe');
	$this->assertSame($skautis, $skautisA);

	$skautisB = Skautis::getInstance('nejake_jine_appid');
	$this->assertNotSame($skautis, $skautisB);
    }

     public function testEventSetter()
     {
         //@TODO
     }

}
