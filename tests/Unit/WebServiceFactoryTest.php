<?php

namespace Test\Skautis;

use Skautis\Wsdl\WebServiceFactory;

class WebServiceFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testCallback()
    {
        $factory = new WebServiceFactory('Test\Skautis\WebServiceStub');
        $args = ['cache' => false];
        $webService = $factory->createWebService("http://moje-wsdl.xml", $args);

        $this->assertEquals("http://moje-wsdl.xml", $webService->getWsdl());
        $this->assertEquals($args, $webService->getSoapArgs());
    }
}


class WebServiceStub
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
