<?php

/**
 * Mocks built-in get_headers function
 */
namespace Skautis\Wsdl;

$GLOBALS['callNumber'] = 0;

function get_headers(string $url){

  try {
    // Pro testNotMaintenance
    if ($GLOBALS['callNumber'] === 0) {
      return ['HTTP/1.1 200 OK'];
    }

    // Pro testMaintenance
    if ($GLOBALS['callNumber'] === 1) {
      return ['HTTP/1.1 503 Service Unavailable'];
    }

    // Pro testMaintenanceNoHeaders
    if ($GLOBALS['callNumber'] === 2) {
      return false;
    }

    // Pro testDNSError
    if ($GLOBALS['callNumber'] === 3) {
      trigger_error(
        'get_headers(): php_network_getaddresses: getaddrinfo failed: Temporary failure in name resolution',
        E_USER_WARNING
      );
    }
  }
  finally {
    $GLOBALS['callNumber']++;
  }
}


namespace Skaut\Skautis\Test\Unit\WsdlManager;

use Mockery;
use PHPUnit\Framework\TestCase;
use Skautis\Config;
use Skautis\Wsdl\MaintenanceErrorException;
use Skautis\Wsdl\WebServiceFactoryInterface;
use Skautis\Wsdl\WsdlManager;

class IsMaintenanceTest extends TestCase
{

  /**
   * @var WsdlManager
   */
  private $manager;

  protected function setUp(): void
  {
    $factory = Mockery::mock(WebServiceFactoryInterface::class);
    $config = new Config('42');
    $this->manager = new WsdlManager($factory, $config);
  }

  /**
   * Když get_headers vrátí pole obsahující HTTP status code OK
   */
  public function testNotMaintenance(): void {
    $this->assertFalse($this->manager->isMaintenance());
  }

  /**
   * Když get_headers vráti pole neobsahující HTTP status code OK
   *
   * @depends testNotMaintenance
   */
  public function testMaintenance(): void {
    $this->assertTrue($this->manager->isMaintenance());
  }

  /**
   * Když get_headers vrátí false místo pole
   *
   * @depends testNotMaintenance
   */
  public function testMaintenanceNoHeaders(): void {
    $this->assertTrue($this->manager->isMaintenance());
  }

  /**
   * Když get_headers způsobí PHP Warning
   *
   * @depends testMaintenanceNoHeaders
   */
  public function testDNSError(): void {
    $this->expectException(MaintenanceErrorException::class);
    $this->manager->isMaintenance();
  }
}

