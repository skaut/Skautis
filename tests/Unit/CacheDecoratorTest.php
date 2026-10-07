<?php

declare(strict_types=1);

namespace Skaut\Skautis\Test\Unit;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Skaut\Skautis\Test\Support\ArrayCache;
use Skaut\Skautis\User;
use Skaut\Skautis\Wsdl\Decorator\Cache\CacheDecorator;
use Skaut\Skautis\Wsdl\WebServiceInterface;

final class CacheDecoratorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testFirstRequestWithLoginIdIsAlwaysSent(): void
    {
        $value = ['id' => 'response'];
        $args = ['asd', 'uv', User::ID_LOGIN => 'a'];

        $webService = Mockery::mock(WebServiceInterface::class);
        $webService->shouldReceive('call')->with('funkceA', $args)->once()->andReturn($value);

        $cache = new ArrayCache();

        $decoratedServiceA = new CacheDecorator($webService, $cache, 30);
        self::assertSame($value, $decoratedServiceA->call('funkceA', $args));

        // another web service instance, cache already filled by the previous request
        $webServiceB = Mockery::mock(WebServiceInterface::class);
        $webServiceB->shouldNotReceive('call');

        $decoratedServiceB = new CacheDecorator($webServiceB, $cache, 30);
        self::assertSame($value, $decoratedServiceB->call('funkceA', $args));
    }

    public function testResponsesAreCachedPerFunctionAndArguments(): void
    {
        $value = ['id' => 'response'];
        $args = ['asd', 'uv'];

        $webService = Mockery::mock(WebServiceInterface::class);
        $webService->shouldReceive('call')->with('funkceA', $args)->once()->andReturn($value);
        $webService->shouldReceive('call')->with('funkceB', $args)->once()->andReturn($value);

        $decoratedService = new CacheDecorator($webService, new ArrayCache(), 30);

        self::assertSame($value, $decoratedService->call('funkceA', $args));
        // __call() behaves like call()
        self::assertSame($value, $decoratedService->__call('funkceA', $args));
        self::assertSame($value, $decoratedService->call('funkceB', $args));

        $args[0] = 'qwe';
        $webService->shouldReceive('call')->with('funkceA', $args)->once()->andReturn($value);
        self::assertSame($value, $decoratedService->call('funkceA', $args));

        unset($args[1]);
        $webService->shouldReceive('call')->with('funkceA', $args)->once()->andReturn($value);
        self::assertSame($value, $decoratedService->call('funkceA', $args));
    }
}
