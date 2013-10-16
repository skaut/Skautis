<?php

namespace Test\SkautIS;

use SkautIS\SkautIS;

class SkautISTest extends \PHPUnit_Framework_TestCase {


    public function testGetWsdlList() {

        $skautIS = SkautIS::getInstance();
        $wdlList = $skautIS->getWsdlList();

        $this->assertInternalType('array', $wdlList);
        $this->assertTrue(count($wdlList) > 0);

        foreach ($wdlList    as $key => $value) {
            $this->assertSame($key, $value);
        }
    }

}
