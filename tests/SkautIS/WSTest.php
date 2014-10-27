<?php

namespace Test\SkautIS;

class WSTest extends \PHPUnit_Framework_TestCase {


  /**
   * @expectedException SkautIS\Exception\AbortException
   */
  public function testWSConstructMissingWsdl() {
    $ws = new \SkautIS\WS("", array());
  }
}
