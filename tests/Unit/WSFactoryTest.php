<?php

namespace Test\Skautis;

use Skautis\Factory\BasicWSFactory;

class WSFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testCallback()
    {
        $factory = new BasicWSFactory('Test\Skautis\WsStub');
	$args = ['cache' => false];
	$ws = $factory->createWS("http://moje-wsdl.xml", $args, true);

	$this->assertEquals("http://moje-wsdl.xml", $ws->getWsdl());
	$this->assertEquals($args, $ws->getSoapArgs());
	$this->assertEquals(true, $ws->getProfiler());
    }
}

class WsStub
{

    protected $wsdl;
    protected $soapArgs;
    protected $profiler;

    public function __construct($wsdl, $soapArgs, $profiler)
    {
	$this->wsdl = $wsdl;
	$this->soapArgs = $soapArgs;
	$this->profiler = $profiler;
    }

    public function getWsdl()
    {
    	return $this->wsdl;
    }

    public function getSoapArgs()
    {
    	return $this->soapArgs;
    }

    public function getProfiler()
    {
    	return $this->profiler;
    }
}
