<?php

namespace Test\Skautis;

use Skautis\Skautis;
use Skautis\SessionAdapter\SessionAdapter;
use Skautis\SessionAdapter\FakeAdapter;

class SkautisTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Ano vim ze to neni officialni pattern
	 * Jedna se o kockopsa Mezi singletonem a StaticFactory
	 * Factory metoda na stride kterou instantizuje a novy objekt vytvari jen 1x za beh
	 * Proc to tak je? Ohled na zpetnou kompatibilitu a pouzitelnost pro amatery
	 */
	public function testSingletonFactoryMethod() {
        $skautISA = Skautis::getInstance();
        $skautISB = Skautis::getInstance();

        $this->assertSame($skautISA, $skautISB);
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

    public function testGetWsdlList() {

        $skautIS = new Skautis();
        $wdlList = $skautIS->getWsdlList();

        $this->assertInternalType('array', $wdlList);
        $this->assertTrue(count($wdlList) > 0);

        foreach ($wdlList as $key => $value) {
            $this->assertSame($key, $value);
        }
    }

    public function testMagicMethodGet() {
        $wsA = new \StdClass;
        $wsB = new \StdClass;

        $factory = \Mockery::mock("\Skautis\Factory\WSFactory");
        $factory->shouldReceive("createWS")->with()->twice()->andReturn($wsA, $wsB);

        $skautIS = new Skautis("123");
        $skautIS->setWSFactory($factory);


        $skautIS->setTestMode(true);
        $eventA = $skautIS->event;
        $this->assertSame($eventA, $skautIS->event);

        $skautIS->setTestMode(false);
        $this->assertNotSame($eventA, $skautIS->event);
        $eventB = $skautIS->event;

        $skautIS->setTestMode(true);
        $this->assertSame($eventA, $skautIS->event);

        $this->assertSame($wsA, $eventA);
        $this->assertSame($wsB, $eventB);
    }

    public function testSetLoginData() {
        $skautIS = new Skautis();
	$data = array(
            'skautIS_Token' => "token",
            'skautIS_IDRole' => 33,
	    'skautIS_IDUnit' => 100,
	    'skautIS_DateLogout' => '2. 12. 2014 23:56:02'
	);
        $skautIS->setLoginData($data);
        $this->assertEquals("token", $skautIS->getToken());
        $this->assertEquals(33, $skautIS->getRoleId());
	$this->assertEquals(100, $skautIS->getUnitId());

	$this->assertEquals("2014-12-02 23:56:02", $skautIS->getLogoutDate()->format('Y-m-d H:i:s'));
    }

    public function testSetLoginDataViaSetters() {
        $skautIS = new Skautis();
        $skautIS->setUnitId(11);
        $this->assertEquals(11, $skautIS->getUnitId());

        $skautIS->setRoleId(44);
        $this->assertEquals(44, $skautIS->getRoleId());

        $skautIS->setUnitId(200);
        $this->assertEquals(200, $skautIS->getUnitId());
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
