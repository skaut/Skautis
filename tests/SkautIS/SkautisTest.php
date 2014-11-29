<?php

namespace Test\Skautis;

use Skautis\Skautis;
use Skautis\SessionAdapter\SessionAdapter;

class SkautisTest extends \PHPUnit_Framework_TestCase {

    public function testSingleton() {
        $skautISA = Skautis::getInstance();
        $skautISB = Skautis::getInstance();

        $this->assertSame($skautISA, $skautISB);
    }

    /**
     * @expectedException Skautis\Exception\InvalidArgumentException
     * @expectedExceptionRegExp / .*test.*mode.* /
     */
    public function testWrongArgumentTestMode() {
        $Skautis = Skautis::getInstance("app_id", "wrong arg");
    }

    /**
     * @expectedException Skautis\Exception\InvalidArgumentException
     * @expectedExceptionRegExp / .*profiler.* /
     */
    public function testWrongArgumentProfiler() {
        $Skautis = Skautis::getInstance("app_id", false, "asd");
    }

    public function testGetWsdlList() {

        $skautIS = Skautis::getInstance();
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

        $skautIS = \Skautis\Skautis::getInstance("123");
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
        $skautIS = Skautis::getInstance();
        $skautIS->setLoginData("token", 33, 100);
        $this->assertEquals("token", $skautIS->getToken());
        $this->assertEquals(33, $skautIS->getRoleId());
        $this->assertEquals(100, $skautIS->getUnitId());

        $skautIS->setUnitId(11);
        $this->assertEquals(11, $skautIS->getUnitId());

        $skautIS->setRoleId(44);
        $this->assertEquals(44, $skautIS->getRoleId());

        $skautIS->setUnitId(200);
        $this->assertEquals(200, $skautIS->getUnitId());

        $skautIS->resetLoginData();
        $this->assertNull($skautIS->getToken());
        $this->assertEquals(0, $skautIS->getRoleId());
        $this->assertEquals(0, $skautIS->getUnitId());
    }

    public function testIsLoggedIn() {
        $ws = \Mockery::mock("\Skautis\WS");
        $ws->shouldReceive("LoginUpdateRefresh")->once()->andReturn();


        $factory = \Mockery::mock("\Skautis\Factory\WSFactory");
        $factory->shouldReceive("createWS")->with()->once()->andReturn($ws);

        $skautIS = Skautis::getInstance();
        $skautIS->setWSFactory($factory);

        $this->assertTrue($skautIS->isLoggedIn());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSessionAdapter() {
        session_start();
        $adapter = new SessionAdapter();
        $skautis = Skautis::getInstance("id123", FALSE, FALSE, $adapter);
        unset($skautis);


        $skautis = Skautis::getInstance(NULL, FALSE, FALSE, $adapter);
        $this->assertSame("id123", $skautis->getAppId());
        unset($adapter);

        $sessionData = session_encode();
        session_destroy();

        session_decode($sessionData);
        $adapterNew = new SessionAdapter();
        $skautis = Skautis::getInstance(NULL, FALSE, FALSE, $adapterNew);
        $this->assertSame("id123", $skautis->getAppId());
    }

}
