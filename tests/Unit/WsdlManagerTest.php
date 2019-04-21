<?php

namespace Test\Skautis;

use Mockery;
use PHPUnit_Framework_TestCase;
use Skautis\Config;
use Skautis\Wsdl\WebServiceFactoryInterface;
use Skautis\Wsdl\WsdlManager;
use Skautis\Wsdl\WebServiceInterface;

class WsdlManagerTest extends PHPUnit_Framework_TestCase
{

    public function testGetSupportedWebServices(): void
    {
        /** @var WebServiceFactoryInterface */
        $factory = Mockery::mock(WebServiceFactoryInterface::class);
        $config = new Config('asd');
        $manager = new WsdlManager($factory, $config);

        $services = $manager->getSupportedWebServices();
        $this->assertTrue(count($services) > 0);
    }

    public function testGetWebService(): void
    {
        $wsA =  Mockery::mock(WebServiceInterface::class);
        $wsB = Mockery::mock(WebServiceInterface::class);

        $factory = Mockery::mock(WebServiceFactoryInterface::class);
        $factory->shouldReceive('createWebService')->twice()->andReturn($wsA, $wsB);

        $config = new Config('42');

        $manager = new WsdlManager($factory, $config);

        $this->assertNotSame($wsA, $wsB);
        $this->assertSame($wsA, $manager->getWebService('UserManagement'));
        $this->assertSame($wsA, $manager->getWebService('UserManagement'));
        $this->assertSame($wsB, $manager->getWebService('ApplicationManagement'));
    }
}
