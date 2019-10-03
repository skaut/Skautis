<?php

namespace Test\Skautis;

use Skautis\InvalidArgumentException;
use Skautis\Wsdl\WebServiceFactory;

class WebServiceFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testEmptyWSDL() {
      $factory = new WebServiceFactory();

      $this->expectException(InvalidArgumentException::class);
      $factory->createWebService('', []);
    }
}

