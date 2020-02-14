<?php

declare(strict_types=1);


namespace Skaut\Skautis\Test\Unit\WebService;



use PHPUnit\Framework\TestCase;
use Skaut\Skautis\Wsdl\WebService;
use Skaut\Skautis\Wsdl\WebServiceInterface;

class ParsingSOAPOutputTest extends TestCase
{

    protected function tearDown(): void
    {
        \Mockery::close();
    }

    private function loadData(string $methodName): \stdClass {
        $filePath = __DIR__.'/resources/'.$methodName.'.txt';

        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \RuntimeException("Cannot access file '$filePath'");
        }

        $text = file_get_contents($filePath);

        return unserialize($text, ['allowed_classes' => [\stdClass::class]]);
    }

    private function createMockedService(\stdClass $data): WebServiceInterface {
        $client = \Mockery::mock(\SoapClient::class);
        $client->shouldReceive('__soapCall')->once()->andReturn($data);

        return new WebService($client, [], null);
    }

    public function testObjectForExistentRecord(): void {
        $service = $this->createMockedService($this->loadData(__FUNCTION__));

        $result = $service->unitDetail(['ID' => 24404]);

        $this->assertNotNull($result);
        $this->assertInstanceOf(\stdClass::class, $result);
        $this->assertSame('Středisko', $result->UnitType);
    }

    public function testNullForNonExistentRecord(): void {
        $service = $this->createMockedService($this->loadData(__FUNCTION__));

        $result = $service->unitDetail(['ID' => 999]);

        $this->assertNull($result);
    }

    public function testArrayOfResults(): void {
        $service = $this->createMockedService($this->loadData(__FUNCTION__));

        $results = $service->unitAll(['ID_UnitParent' => 24404]);

        $this->assertIsArray($results);
        $this->assertCount(5, $results);

        foreach ($results as $result) {
            $this->assertInstanceOf(\stdClass::class, $result);
        }
    }

    public function testEmptyArrayOfResults(): void {
        $service = $this->createMockedService($this->loadData(__FUNCTION__));

        $result = $service->unitAll(['ID_UnitParent' => 999]);

        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }
}
