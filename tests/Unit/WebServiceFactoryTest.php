<?php

namespace Test\Skautis;

use PHPUnit\Framework\TestCase;
use Skautis\InvalidArgumentException;
use Skautis\Wsdl\WebServiceFactory;

class WebServiceFactoryTest extends TestCase
{

    public function testEmptyWSDL() {
      $factory = new WebServiceFactory();

      $this->expectException(InvalidArgumentException::class);
      $factory->createWebService('', []);
    }
}

