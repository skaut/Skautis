<?php

declare(strict_types=1);

namespace Skaut\Skautis\Test\Unit;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Skaut\Skautis\InvalidArgumentException;
use Skaut\Skautis\Wsdl\WebServiceFactory;
use stdClass;

final class WebServiceFactoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testEmptyWsdlUrlIsRejected(): void
    {
        $factory = new WebServiceFactory();

        $this->expectException(InvalidArgumentException::class);
        $factory->createWebService('', []);
    }

    public function testClassMustImplementWebServiceInterface(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new WebServiceFactory(stdClass::class);
    }

    public function testEventDispatcherCanBeSetOnlyOnce(): void
    {
        $factory = new WebServiceFactory();
        $factory->setEventDispatcher(Mockery::mock(EventDispatcherInterface::class));

        $this->expectException(InvalidArgumentException::class);
        $factory->setEventDispatcher(Mockery::mock(EventDispatcherInterface::class));
    }
}
