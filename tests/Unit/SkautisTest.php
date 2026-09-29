<?php

declare(strict_types=1);

namespace Skaut\Skautis\Test\Unit;

use DateTimeImmutable;
use DateTimeZone;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Skaut\Skautis\Config;
use Skaut\Skautis\DynamicPropertiesDisabledException;
use Skaut\Skautis\SessionAdapter\FakeAdapter;
use Skaut\Skautis\Skautis;
use Skaut\Skautis\User;
use Skaut\Skautis\Wsdl\WebServiceFactoryInterface;
use Skaut\Skautis\Wsdl\WebServiceInterface;
use Skaut\Skautis\Wsdl\WebServiceName;
use Skaut\Skautis\Wsdl\WebServiceNotFoundException;
use Skaut\Skautis\Wsdl\WsdlManager;

final class SkautisTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testSharedInstancePerAppIdAndMode(): void
    {
        self::assertSame(Skautis::getInstance('asd'), Skautis::getInstance('asd'));
        self::assertNotSame(Skautis::getInstance('asd'), Skautis::getInstance('qwe'));
        self::assertNotSame(
            Skautis::getInstance('some-app-id', Config::TEST_MODE_ENABLED),
            Skautis::getInstance('some-app-id', Config::TEST_MODE_DISABLED),
        );
    }

    public function testWebServiceByNameAndAlias(): void
    {
        $skautis = $this->createSkautis();

        $service = $skautis->UserManagement;

        self::assertSame($service, $skautis->getWebService(WebServiceName::USER_MANAGEMENT));
        self::assertSame($service, $skautis->getWebService('user'));
        self::assertSame($service, $skautis->getWebService('usr'));
        self::assertSame($service, $skautis->__get('usr'));
    }

    public function testUnknownWebService(): void
    {
        $skautis = $this->createSkautis();

        $this->expectException(WebServiceNotFoundException::class);
        $skautis->getWebService('nonsense');
    }

    public function testWebServicesCannotBeOverwritten(): void
    {
        $skautis = $this->createSkautis();

        $this->expectException(DynamicPropertiesDisabledException::class);
        $skautis->__set('UserManagement', 'asd');
    }

    public function testLoginUrl(): void
    {
        $skautis = $this->createSkautis(Config::TEST_MODE_DISABLED);

        self::assertSame('https://is.skaut.cz/Login/?appid=asd', $skautis->getLoginUrl());
        self::assertSame(
            'https://is.skaut.cz/Login/?appid=asd&ReturnUrl=https%3A%2F%2Fmy-web.nowhere%2Fasd',
            $skautis->getLoginUrl('https://my-web.nowhere/asd'),
        );
    }

    public function testLogoutUrl(): void
    {
        $skautis = $this->createSkautis();
        $skautis->getUser()->setLoginData('log://123out');

        self::assertSame(
            'https://test-is.skaut.cz/Login/LogOut.aspx?appid=asd&token=log%3A%2F%2F123out',
            $skautis->getLogoutUrl(),
        );
    }

    public function testRegisterUrl(): void
    {
        $skautis = $this->createSkautis(Config::TEST_MODE_DISABLED);

        self::assertSame('https://is.skaut.cz/Login/Registration.aspx?appid=asd', $skautis->getRegisterUrl());
    }

    public function testSetLoginDataFromPost(): void
    {
        $skautis = $this->createSkautis();

        $skautis->setLoginData([
            'skautIS_Token' => 'token',
            'skautIS_IDRole' => '33',
            'skautIS_IDUnit' => '100',
            'skautIS_DateLogout' => '2. 12. 2044 23:56:02',
        ]);

        $user = $skautis->getUser();
        self::assertSame('token', $user->getLoginId());
        self::assertSame(33, $user->getRoleId());
        self::assertSame(100, $user->getUnitId());
        self::assertEquals(new DateTimeImmutable('2044-12-02 23:56:02', new DateTimeZone('Europe/Prague')), $user->getLogoutDate());
    }

    private function createSkautis(bool $testMode = Config::TEST_MODE_ENABLED): Skautis
    {
        $factory = Mockery::mock(WebServiceFactoryInterface::class);
        $factory->shouldReceive('createWebService')->andReturnUsing(
            static fn (): WebServiceInterface => Mockery::mock(WebServiceInterface::class),
        );

        $wsdlManager = new WsdlManager($factory, new Config('asd', $testMode));

        return new Skautis($wsdlManager, new User($wsdlManager, new FakeAdapter()));
    }
}
