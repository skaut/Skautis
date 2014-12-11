<?php

namespace Test\Skautis;

use Skautis\Skautis;
use Skautis\SessionAdapter\SessionAdapter;
use Skautis\SessionAdapter\FakeAdapter;

class SkautisTest extends \PHPUnit_Framework_TestCase 
{

    protected function makeSession()
    {
        return new Skautis\SessionAdapter\FakeAdapter();
    }

    protected function makeFactory()
    {
        return \Mockery::mock("\Skautis\Factory\WSFactory");
    }

    protected function makeConfig()
    {
        return \Mockery::mock("\Skautis\Config");
    }
    /**
     * @expectedException Skautis\Exception\InvalidArgumentException
     * @expectedExceptionRegExp / .*test.*mode.* /
     */
    public function testWrongArgumentTestMode() {
        new Skautis("app_id", "wrong arg");
    }

    /**
     * @expectedException Skautis\Exception\InvalidArgumentException
     * @expectedExceptionRegExp / .*profiler.* /
     */
    public function testWrongArgumentProfiler() {
        new Skautis("app_id", false, "asd");
    }



    public function testSetLoginData() {
        $skautIS = new Skautis();
	$data = array(
            'skautIS_Token' => "token",
            'skautIS_IDRole' => 33,
	    'skautIS_IDUnit' => 100,
	    'skautIS_DateLogout' => '2. 12. 2014 23:56:02'
	);
	$this->assertFalse($skautIS->isLoggedIn());

        $skautIS->setLoginData($data);
        $this->assertEquals("token", $skautIS->getToken());
        $this->assertEquals(33, $skautIS->getRoleId());
	$this->assertEquals(100, $skautIS->getUnitId());

	$this->assertEquals("2014-12-02 23:56:02", $skautIS->getLogoutDate()->format('Y-m-d H:i:s'));
    }


    public function testIsLoggedInNoInicialization() {
        $skautIS = new Skautis();

        $this->assertFalse($skautIS->isLoggedIn());
    }

    public function testIsLoggedIn() {
        $skautIS = new Skautis("ad123");
	$skautIS->setToken("token");

	$skautIS->setLogoutDate(new \DateTime('yesterday'));
	$this->assertFalse($skautIS->isLoggedIn());
    }

    public function testIsLoggedHardCheck() {
        $ws = \Mockery::mock("\Skautis\WS");
        $ws->shouldReceive("LoginUpdateRefresh")->once()->andReturn();


        $factory = \Mockery::mock("\Skautis\Factory\WSFactory");
        $factory->shouldReceive("createWS")->with()->once()->andReturn($ws);

        $skautIS = new Skautis("ad123");
	$skautIS->setWSFactory($factory);
	$skautIS->setToken("tooken");
	$skautIS->setLogoutDate(new \DateTime('tomorrow'));

        $this->assertTrue($skautIS->isLoggedIn(true));
    }

    public function testResetLoginData() {
	$data = array(
            'skautIS_Token' => "token",
            'skautIS_IDRole' => 33,
	    'skautIS_IDUnit' => 100,
	    'skautIS_DateLogout' => '2. 12. 2014 23:56:02'
	);

	$skautis = new Skautis();

	$skautis->setLoginData($data);
	$this->assertEquals(33, $skautis->getRoleId());

	$skautis->resetLoginData();
        $this->assertEmpty($skautis->getToken());
        $this->assertEmpty($skautis->getRoleId());
	$this->assertEmpty($skautis->getUnitId());
	$this->assertNull($skautis->getLogoutDate());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSessionAdapter() {
        $adapter = new FakeAdapter();
        $skautis = new Skautis("id123", FALSE, FALSE, $adapter);

	$skautis = new Skautis(NULL, FALSE, FALSE, $adapter);
        $this->assertSame("id123", $skautis->getAppId());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSessionAdapterSetter() {
        session_start();
        $adapter = new SessionAdapter();
	$skautis = new Skautis("id123", FALSE, FALSE);
	$skautis->setAdapter($adapter);


        $skautis = new Skautis(NULL, FALSE, FALSE, $adapter);
        $this->assertSame("id123", $skautis->getAppId());
        unset($adapter);

        $sessionData = session_encode();
        session_destroy();

        session_decode($sessionData);
        $adapterNew = new SessionAdapter();
        $skautis = new Skautis(NULL, FALSE, FALSE, $adapterNew);
        $this->assertSame("id123", $skautis->getAppId());
    }

    public function testEventSetter()
    {
	//@TODO
    }

}
