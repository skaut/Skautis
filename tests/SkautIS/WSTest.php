<?php

namespace Test\SkautIS;

class WSTest extends \PHPUnit_Framework_TestCase {

  public function testWSConstructMissingWsdl() {
    try {
      $ws = new \SkautIS\WS();
    }
    catch (\Exception $e) {
      return;
    }

    $this->fail('Ws se vytvori i bez $WSDL');
  }
}
