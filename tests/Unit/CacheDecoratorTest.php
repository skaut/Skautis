<?php

use Skautis\Wsdl\Decorator\Cache\ArrayCache;
use Skautis\Wsdl\Decorator\Cache\CacheDecorator;

class CacheDecoratorTest extends \PHPUnit_Framework_TestCase
{

    protected function tearDown()
    {
        \Mockery::close();
    }

    public function testDecorator()
    {
	$value = ['id' => 'response'];
	$args = ['id' => 'asd', 'txt' => 'uv'];

	$webService = \Mockery::mock('Skautis\Wsdl\WebServiceInterface');
	$webService->shouldReceive('call')->with('funkceA', $args)->once()->andReturn($value);
	$webService->shouldReceive('call')->with('funkceB', $args)->once()->andReturn($value);

	$cache = new ArrayCache();

	$decoratedService = new CacheDecorator($webService, $cache);




	// Stejne volani pouze 1x WebService
	$response = $decoratedService->call('funkceA', $args);
	$this->assertEquals($value, $response);

	$response = $decoratedService->call('funkceA', $args);
	$this->assertEquals($value, $response);

	// Stejne parametry jina funkce
	$response = $decoratedService->call('funkceB', $args);
	$this->assertEquals($value, $response);


	// Zmena obsahu parametru
	$args['id'] = 'qwe';
	$webService->shouldReceive('call')->with('funkceA', $args)->once()->andReturn($value);

	$response = $decoratedService->call('funkceA', $args);
	$this->assertEquals($value, $response);


	// Odebrani parametru
	unset($args['txt']);
	$webService->shouldReceive('call')->with('funkceA', $args)->once()->andReturn($value);

	$response = $decoratedService->call('funkceA', $args);
	$this->assertEquals($value, $response);
    }
}
