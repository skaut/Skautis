<?php

declare(strict_types=1);


namespace Skaut\Skautis\Test\Unit;


use PHPUnit\Framework\TestCase;
use Skaut\Skautis\Wsdl\WebServiceName;

class WebServiceNameTest extends TestCase
{

  public function testIsValid(): void
  {
    $this->assertTrue(WebServiceName::isValidServiceName(WebServiceName::APPLICATION_MANAGEMENT));
    $this->assertTrue(WebServiceName::isValidServiceName(WebServiceName::WELCOME));
  }

  public function testIsNotValid(): void
  {
    $this->assertFalse(WebServiceName::isValidServiceName('usr'));
    $this->assertFalse(WebServiceName::isValidServiceName('user'));
  }
}