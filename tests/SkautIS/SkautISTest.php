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

    public function test__get() {

        $skautIS = \SkautIS\SkautIS::getInstance("123");


        $skautIS->setTestMode(true);
        $userA = $skautIS->user;
        $this->assertSame($userA, $skautIS->user);

        $skautIS->setTestMode(false);
        $this->assertNotSame($userA, $skautIS->user);

        $skautIS->setTestMode(true);
        $this->assertSame($userA, $skautIS->user);
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
        $skautIS = SkautIS::getInstance();
        $this->assertFalse($skautIS->isLoggedIn());
    }

}
