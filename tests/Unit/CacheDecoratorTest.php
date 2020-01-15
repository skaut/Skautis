<?php

namespace Skaut\Skautis\Test\Unit;

use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Skaut\Skautis\User;
use Skaut\Skautis\Wsdl\Decorator\Cache\CacheDecorator;
use Skaut\Skautis\Wsdl\WebServiceInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

class CacheDecoratorTest extends TestCase
{

    protected function tearDown(): void
    {
        \Mockery::close();
    }

    public function testDecoratorRequireRequest()
    {
        $value = ['id' => 'response'];
        $args = ['asd', 'uv', User::ID_LOGIN => 'a'];

        /** @var WebServiceInterface|MockInterface $webService */
        $webService = \Mockery::mock(WebServiceInterface::class);
        $webService->shouldReceive('call')->with('funkceA', $args)->once()->andReturn($value);

        $cache = new Psr16Cache(new ArrayAdapter());

        //Prazdna cache, musi poslat request
        $decoratedServiceA = new CacheDecorator($webService, $cache, 30);
        $response = $decoratedServiceA->call('funkceA', $args);
        $this->assertEquals($value, $response);


        //Jina instance WS
        /** @var WebServiceInterface|MockInterface $decoratedServiceB */
        $webServiceB = \Mockery::mock(WebServiceInterface::class);

        //Cache naplnena z prechoziho requestu
        $decoratedServiceB = new CacheDecorator($webServiceB, $cache, 30);
        $response = $decoratedServiceB->call('funkceA', $args);

        //Vraci data z cache
        $this->assertEquals($value, $response);
    }

    public function testDecorator()
    {
        $value = ['id' => 'response'];
        $args = ['asd', 'uv'];

        /** @var WebServiceInterface|MockInterface $webService */
        $webService = \Mockery::mock(WebServiceInterface::class);
        $webService->shouldReceive('call')->with('funkceA', $args)->once()->andReturn($value);
        $webService->shouldReceive('call')->with('funkceB', $args)->once()->andReturn($value);

        $cache = new Psr16Cache(new ArrayAdapter());
        $decoratedService = new CacheDecorator($webService, $cache, 30);




        // Stejne volani pouze 1x WebService
        $response = $decoratedService->call('funkceA', $args);
        $this->assertEquals($value, $response);

        // __call() stejny jako call()
        $response = $decoratedService->funkceA($args[0], $args[1]);
        $this->assertEquals($value, $response);

        // Stejne parametry jina funkce
        $response = $decoratedService->call('funkceB', $args);
        $this->assertEquals($value, $response);


        // Zmena obsahu parametru
        $args[0] = 'qwe';
        $webService->shouldReceive('call')->with('funkceA', $args)->once()->andReturn($value);

        $response = $decoratedService->call('funkceA', $args);
        $this->assertEquals($value, $response);


        // Odebrani parametru
        unset($args['1']);
        $webService->shouldReceive('call')->with('funkceA', $args)->once()->andReturn($value);

        $response = $decoratedService->call('funkceA', $args);
        $this->assertEquals($value, $response);
    }
}
