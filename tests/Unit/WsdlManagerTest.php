<?php

namespace Test\Skautis;

use Skautis\Config;
use Skautis\Wsdl\WsdlManager;
use Skautis\Wsdl\WebServiceInterface;
use Skautis\Wsdl\WebServiceFactory;

class WsdlManagerTest extends \PHPUnit_Framework_TestCase
{

    public function testGetSupportedWebServices()
    {
        /** @var \Skautis\Wsdl\WebServiceFactory */
        $factory = \Mockery::mock('\Skautis\Wsdl\WebServiceFactory');
        /** @var \Skautis\Confi */
        $config = \Mockery::mock('\Skautis\Config');
        $manager = new WsdlManager($factory, $config);

        $services = $manager->getSupportedWebServices();
        $this->assertInternalType('array', $services);
        $this->assertTrue(count($services) > 0);
    }

    public function testGetWebService()
    {
        $wsA =  \Mockery::mock(WebServiceInterface::class);
        $wsB =\Mockery::mock(WebServiceInterface::class);

        $factory = \Mockery::mock(WebServiceFactory::class);
        $factory->shouldReceive('createWebService')->withAnyArgs()->twice()->andReturn($wsA, $wsB);
        $config = new Config('42');

        $manager = new WsdlManager($factory, $config);

        $config->setTestMode(true);
        $eventA = $manager->getWebService('UserManagement');
        $this->assertSame($eventA, $manager->getWebService('UserManagement'));

        $config->setTestMode(false);
        $eventB = $manager->getWebService('UserManagement');
        $this->assertNotSame($eventA, $eventB);

        $config->setTestMode(true);
        $this->assertSame($eventA, $manager->getWebService('UserManagement'));

        $this->assertSame($wsA, $eventA);
        $this->assertSame($wsB, $eventB);
    }
}
