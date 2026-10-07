<?php

declare(strict_types=1);

/*
 * Overrides the built-in get_headers() for the Skaut\Skautis\Wsdl namespace; the responses
 * follow the order of the tests below.
 */

namespace Skaut\Skautis\Wsdl;

/**
 * @return array<int, string>|false
 */
function get_headers(string $url): array|false
{
    /** @var list<array<int, string>|false|'network-error'> $responses */
    static $responses = [
        ['HTTP/1.1 200 OK'],
        ['HTTP/2 200'],
        ['HTTP/1.1 503 Service Unavailable'],
        false,
        'network-error',
    ];
    /** @var int $call */
    static $call = 0;

    $response = $responses[$call] ?? false;
    ++$call;
    if ($response === 'network-error') {
        trigger_error(
            'get_headers(): php_network_getaddresses: getaddrinfo failed: Temporary failure in name resolution',
            \E_USER_WARNING,
        );

        return false;
    }

    return $response;
}

namespace Skaut\Skautis\Test\Unit\WsdlManager;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use Skaut\Skautis\Config;
use Skaut\Skautis\Wsdl\MaintenanceErrorException;
use Skaut\Skautis\Wsdl\WebServiceFactoryInterface;
use Skaut\Skautis\Wsdl\WsdlManager;

final class IsMaintenanceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private WsdlManager $manager;

    protected function setUp(): void
    {
        $this->manager = new WsdlManager(Mockery::mock(WebServiceFactoryInterface::class), new Config('42'));
    }

    public function testHttp1Ok(): void
    {
        self::assertFalse($this->manager->isMaintenance());
    }

    #[Depends('testHttp1Ok')]
    public function testHttp2Ok(): void
    {
        self::assertFalse($this->manager->isMaintenance());
    }

    #[Depends('testHttp2Ok')]
    public function testServiceUnavailable(): void
    {
        self::assertTrue($this->manager->isMaintenance());
    }

    #[Depends('testServiceUnavailable')]
    public function testNoHeaders(): void
    {
        self::assertTrue($this->manager->isMaintenance());
    }

    #[Depends('testNoHeaders')]
    public function testNetworkErrorBecomesException(): void
    {
        try {
            $this->manager->isMaintenance();
            self::fail('MaintenanceErrorException expected');
        } catch (MaintenanceErrorException $exception) {
            self::assertStringContainsString('getaddrinfo failed', $exception->getMessage());
            self::assertSame(\E_USER_WARNING, $exception->getErrorNumber());
            self::assertSame(__FILE__, $exception->getErrorFile());
            self::assertGreaterThan(0, $exception->getErrorLine());
        }
    }
}
