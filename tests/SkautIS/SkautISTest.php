<?php

namespace Test\SkautIS;

use SkautIS\SkautIS;

class SkautISTest extends \PHPUnit_Framework_TestCase {

    public function testSingleton() {
        $skautISA = SkautIS::getInstance();
        $skautISB = SkautIS::getInstance();

        $this->assertSame($skautISA, $skautISB);
    }

    /**
     * @expectedException SkautIS\Exception\InvalidArgumentException
     * @expectedExceptionRegExp / .*test.*mode.* /
     */
    public function testWrongArgumentTestMode() {
       $skautis = SkautIS::getInstance("app_id", "wrong arg");
    }

    /**
     * @expectedException SkautIS\Exception\InvalidArgumentException
     * @expectedExceptionRegExp / .*profiler.* /
     */
    public function testWrongArgumentProfiler() {
       $SkautIS = SkautIS::getInstance("app_id", false, "asd");
    }


    public function testGetWsdlList() {

        $skautIS = SkautIS::getInstance();
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

        $factory = \Mockery::mock("\SkautIS\Factory\WSFactory");
        $factory->shouldReceive("createWS")->with()->twice()->andReturn($wsA, $wsB);

        $skautIS = \SkautIS\SkautIS::getInstance("123");
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
        $skautIS = SkautIS::getInstance();
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
        $ws = \Mockery::mock("\SkautIS\WS");
        $ws->shouldReceive("LoginUpdateRefresh")->once()->andReturn();


        $factory = \Mockery::mock("\SkautIS\Factory\WSFactory");
        $factory->shouldReceive("createWS")->with()->once()->andReturn($ws);

        $skautIS = SkautIS::getInstance();
        $skautIS->setWSFactory($factory);

        $this->assertTrue($skautIS->isLoggedIn());
    }
}
