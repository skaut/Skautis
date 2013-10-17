<?php

namespace Test\SkautIS;

use SkautIS\SkautIS;

class SkautISTest extends \PHPUnit_Framework_TestCase {

    public function testSingleton()
    {
        $skautISA = SkautIS::getInstance();
        $skautISB = SkautIS::getInstance();

        $this->assertSame($skautISA, $skautISB);
    }

    public function testGetWsdlList() {

        $skautIS = SkautIS::getInstance();
        $wdlList = $skautIS->getWsdlList();

        $this->assertInternalType('array', $wdlList);
        $this->assertTrue(count($wdlList) > 0);

        foreach ($wdlList    as $key => $value) {
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

}
