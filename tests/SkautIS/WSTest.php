<?php

namespace Test\Skautis;

class WSTest extends \PHPUnit_Framework_TestCase {

    /**
     * @expectedException SkautIS\Exception\AbortException
     */
    public function testWSConstructMissingWsdl() {
        $ws = new \Skautis\WS("", array());
    }

}
