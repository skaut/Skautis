<?php

declare(strict_types=1);

namespace Skaut\Skautis\Test\Unit\WsdlManager;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Skaut\Skautis\Config;
use Skaut\Skautis\User;
use Skaut\Skautis\Wsdl\WebServiceFactoryInterface;
use Skaut\Skautis\Wsdl\WebServiceInterface;
use Skaut\Skautis\Wsdl\WebServiceName;
use Skaut\Skautis\Wsdl\WebServiceNotFoundException;
use Skaut\Skautis\Wsdl\WsdlManager;

final class GetWebServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testWebServicesAreCreatedOncePerNameAndLogin(): void
    {
        $wsA = Mockery::mock(WebServiceInterface::class);
        $wsB = Mockery::mock(WebServiceInterface::class);
        $wsC = Mockery::mock(WebServiceInterface::class);

        $factory = Mockery::mock(WebServiceFactoryInterface::class);
        $factory->shouldReceive('createWebService')
            ->with('https://test-is.skaut.cz/JunakWebservice/UserManagement.asmx?WSDL', Mockery::on(
                static fn (array $options): bool => $options['ID_Application'] === '42' && $options[User::ID_LOGIN] === null,
            ))
            ->once()
            ->andReturn($wsA);
        $factory->shouldReceive('createWebService')
            ->with('https://test-is.skaut.cz/JunakWebservice/ApplicationManagement.asmx?WSDL', Mockery::type('array'))
            ->once()
            ->andReturn($wsB);
        $factory->shouldReceive('createWebService')
            ->with('https://test-is.skaut.cz/JunakWebservice/UserManagement.asmx?WSDL', Mockery::on(
                static fn (array $options): bool => $options[User::ID_LOGIN] === 'token',
            ))
            ->once()
            ->andReturn($wsC);

        $manager = new WsdlManager($factory, new Config('42'));

        self::assertSame($wsA, $manager->getWebService(WebServiceName::USER_MANAGEMENT));
        self::assertSame($wsA, $manager->getWebService(WebServiceName::USER_MANAGEMENT));
        self::assertSame($wsB, $manager->getWebService(WebServiceName::APPLICATION_MANAGEMENT));
        self::assertSame($wsC, $manager->getWebService(WebServiceName::USER_MANAGEMENT, 'token'));
    }

    public function testUnknownWebService(): void
    {
        $manager = new WsdlManager(Mockery::mock(WebServiceFactoryInterface::class), new Config('42'));

        $this->expectException(WebServiceNotFoundException::class);
        $manager->getWebService('usr');
    }
}
