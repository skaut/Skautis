<?php

namespace Test\Skautis;

use Skautis\User;
use Skautis\Wsdl\Decorator\Cache\ArrayCache;
use Skautis\Wsdl\Decorator\Cache\CacheDecorator;

class CacheDecoratorTest extends \PHPUnit_Framework_TestCase
{

    protected function tearDown()
    {
        \Mockery::close();
    }

    public function testDecoratorRequireRequest()
    {
        $value = ['id' => 'response'];
        $args = ['asd', 'uv', User::ID_LOGIN => 'a'];

        /** @var Skautis\Wsdl\WebServiceInterface */
        $webService = \Mockery::mock('Skautis\Wsdl\WebServiceInterface');
        $webService->shouldReceive('call')->with('funkceA', $args)->once()->andReturn($value);

        $cache = new ArrayCache();

        //Prazdna cache, musi poslat request
        $decoratedServiceA = new CacheDecorator($webService, $cache);
        $response = $decoratedServiceA->call('funkceA', $args);
        $this->assertEquals($value, $response);


        //Jina instance WS
        /** @var Skautis\Wsdl\WebServiceInterface */
        $webServiceB = \Mockery::mock('Skautis\Wsdl\WebServiceInterface');

        //Cache naplnena z prechoziho requestu
        $decoratedServiceB = new CacheDecorator($webServiceB, $cache);
        $response = $decoratedServiceB->call('funkceA', $args);

        //Vraci data z cache
        $this->assertEquals($value, $response);
    }

    public function testDecorator()
    {
        $value = ['id' => 'response'];
        $args = ['asd', 'uv'];

        /** @var Skautis\Wsdl\WebServiceInterface */
        $webService = \Mockery::mock('Skautis\Wsdl\WebServiceInterface');
        $webService->shouldReceive('call')->with('funkceA', $args)->once()->andReturn($value);
        $webService->shouldReceive('call')->with('funkceB', $args)->once()->andReturn($value);

        $cache = new ArrayCache();

        $decoratedService = new CacheDecorator($webService, $cache);




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
