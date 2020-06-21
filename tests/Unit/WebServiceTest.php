<?php

namespace Skaut\Skautis\Test\Unit;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Skaut\Skautis;
use Skaut\Skautis\Exception as SkautisException;
use Skaut\Skautis\Wsdl\WebService;
use Skaut\Skautis\Wsdl\WebServiceFactory;

class WebServiceTest extends TestCase
{

    /**
     * @var WebServiceFactory
     */
    private $wsFactory;

    /**
     * @var MockInterface|EventDispatcherInterface
     */
    private $eventDispatcher;

    protected function setUp(): void
    {
        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);

        $this->wsFactory = new WebServiceFactory(
          WebService::class,
          $this->eventDispatcher
        );
    }

    public function testFailCall(): void
    {
        $data = [
          'ID_Application' => 123,
          Skautis\User::ID_LOGIN => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
        ];
        $webService = $this->wsFactory->createWebService(
          'https://test-is.skaut.cz/JunakWebservice/UserManagement.asmx?WSDL',
          $data
        );

        $preEventCheck = static function ($obj): bool {
            return $obj instanceof Skautis\Wsdl\Event\RequestPreEvent;
        };

        $failEventCheck = static function ($obj): bool {
            return $obj instanceof Skautis\Wsdl\Event\RequestFailEvent;
        };

        $this->eventDispatcher->expects('dispatch')->withArgs($preEventCheck)->once();
        $this->eventDispatcher->expects('dispatch')->withArgs($failEventCheck)->once();

        $this->expectException(SkautisException::class);
        $webService->UserDetail();
    }
}
