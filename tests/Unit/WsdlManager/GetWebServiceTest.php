<?php

namespace Test\Skautis;

use Mockery;
use PHPUnit\Framework\TestCase;
use Skautis\Config;
use Skautis\Wsdl\WebServiceFactoryInterface;
use Skautis\Wsdl\WebServiceName;
use Skautis\Wsdl\WsdlManager;
use Skautis\Wsdl\WebServiceInterface;

class GetWebServiceTest extends TestCase
{

    public function testGetWebService(): void
    {
        $wsA =  Mockery::mock(WebServiceInterface::class);
        $wsB = Mockery::mock(WebServiceInterface::class);

        $factory = Mockery::mock(WebServiceFactoryInterface::class);
        $factory->shouldReceive('createWebService')->twice()->andReturn($wsA, $wsB);

        $config = new Config('42');

        $manager = new WsdlManager($factory, $config);

        $this->assertNotSame($wsA, $wsB);
        $this->assertSame($wsA, $manager->getWebService(WebServiceName::USER_MANAGEMENT));
        $this->assertSame($wsA, $manager->getWebService(WebServiceName::USER_MANAGEMENT));
        $this->assertSame($wsA, $manager->getWebService(WebServiceName::USER_MANAGEMENT));
        $this->assertSame($wsB, $manager->getWebService(WebServiceName::APPLICATION_MANAGEMENT));
    }
}

