<?php

declare(strict_types=1);

namespace Skaut\Skautis\Test\Unit\WebService;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Skaut\Skautis\Wsdl\WebService;
use Skaut\Skautis\Wsdl\WebServiceInterface;
use SoapClient;
use stdClass;

/**
 * Responses recorded from the test skautIS, serialized as SoapClient returns them.
 */
final class ParsingSOAPOutputTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testObjectForExistentRecord(): void
    {
        $service = $this->createMockedWebService($this->loadData(__FUNCTION__));

        $result = $service->call('UnitDetail', [['ID' => 24404]]);

        self::assertInstanceOf(stdClass::class, $result);
        self::assertSame('Středisko', $result->UnitType);
    }

    public function testNullForNonExistentRecord(): void
    {
        $service = $this->createMockedWebService($this->loadData(__FUNCTION__));

        self::assertNull($service->call('UnitDetail', [['ID' => 999]]));
    }

    public function testArrayOfResults(): void
    {
        $service = $this->createMockedWebService($this->loadData(__FUNCTION__));

        $results = $service->call('UnitAll', [['ID_UnitParent' => 24404]]);

        self::assertIsArray($results);
        self::assertCount(5, $results);
        self::assertContainsOnlyInstancesOf(stdClass::class, $results);
    }

    public function testEmptyArrayOfResults(): void
    {
        $service = $this->createMockedWebService($this->loadData(__FUNCTION__));

        self::assertSame([], $service->call('UnitAll', [['ID_UnitParent' => 999]]));
    }

    private function loadData(string $methodName): stdClass
    {
        $filePath = __DIR__.'/resources/'.$methodName.'.txt';
        $text = @file_get_contents($filePath);
        if ($text === false) {
            throw new RuntimeException("Cannot read file '$filePath'");
        }

        $data = unserialize(rtrim($text), ['allowed_classes' => [stdClass::class]]);
        if (! $data instanceof stdClass) {
            throw new RuntimeException("Unexpected content of '$filePath'");
        }

        return $data;
    }

    private function createMockedWebService(stdClass $data): WebServiceInterface
    {
        $client = Mockery::mock(SoapClient::class);
        $client->shouldReceive('__soapCall')->once()->andReturn($data);

        return new WebService($client, []);
    }
}
