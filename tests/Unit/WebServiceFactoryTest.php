<?php

namespace Test\Skautis;

use Skautis\Wsdl\WebServiceFactory;

class WebServiceFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testCallback()
    {
        $factory = new WebServiceFactory('Test\Skautis\WsStub');
	$args = ['cache' => false];
	$ws = $factory->createWebService("http://moje-wsdl.xml", $args);

	$this->assertEquals("http://moje-wsdl.xml", $ws->getWsdl());
	$this->assertEquals($args, $ws->getSoapArgs());
    }
}

class WsStub
{

    protected $wsdl;
    protected $soapArgs;

    public function __construct($wsdl, $soapArgs)
    {
	$this->wsdl = $wsdl;
	$this->soapArgs = $soapArgs;
    }

    public function getWsdl()
    {
    	return $this->wsdl;
    }

    public function getSoapArgs()
    {
    	return $this->soapArgs;
    }

}
