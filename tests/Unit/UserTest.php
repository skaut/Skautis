<?php

declare(strict_types=1);

namespace Skaut\Skautis\Test\Unit;

use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Skaut\Skautis\SessionAdapter\FakeAdapter;
use Skaut\Skautis\UnexpectedValueException;
use Skaut\Skautis\User;
use Skaut\Skautis\Wsdl\AuthenticationException;
use Skaut\Skautis\Wsdl\WebServiceInterface;
use Skaut\Skautis\Wsdl\WebServiceName;
use Skaut\Skautis\Wsdl\WsdlManager;
use stdClass;
use Throwable;

final class UserTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testSetLoginData(): void
    {
        $logoutDate = new DateTimeImmutable('+1 hour');
        $user = new User($this->createWsdlManager());
        self::assertFalse($user->isLoggedIn());

        $user->setLoginData('token', 33, 100, $logoutDate);

        self::assertSame('token', $user->getLoginId());
        self::assertSame(33, $user->getRoleId());
        self::assertSame(100, $user->getUnitId());
        self::assertSame($logoutDate, $user->getLogoutDate());
    }

    public function testUpdateLoginDataKeepsOtherValues(): void
    {
        $user = new User($this->createWsdlManager());
        $user->setLoginData('token', 33, 100);

        $user->updateLoginData(roleId: 44);

        self::assertSame('token', $user->getLoginId());
        self::assertSame(44, $user->getRoleId());
        self::assertSame(100, $user->getUnitId());
    }

    public function testLoginDataArePersistedInSession(): void
    {
        $session = new FakeAdapter();
        $wsdlManager = $this->createWsdlManager();

        (new User($wsdlManager, $session))->setLoginData('token', 33, 100);
        $restored = new User($wsdlManager, $session);

        self::assertSame('token', $restored->getLoginId());
        self::assertSame(33, $restored->getRoleId());
        self::assertSame(100, $restored->getUnitId());
    }

    public function testHardCheckExtendsLogin(): void
    {
        $response = new stdClass();
        $response->DateLogout = '2044-02-12T15:19:21.996';

        $user = new User($this->createWsdlManager($response));
        $user->setLoginData('token', 33, 100, new DateTimeImmutable('+1 day'));

        self::assertTrue($user->isLoggedIn(true));
        self::assertSame('2044-02-12 15:19:21', $user->getLogoutDate()?->format('Y-m-d H:i:s'));
    }

    public function testLoginIsNotConfirmedWhenSkautisRejectsIt(): void
    {
        $user = new User($this->createWsdlManager(new AuthenticationException('Uživatel byl odhlášen')));
        $user->setLoginData('token', 33, 100, new DateTimeImmutable('+1 day'));

        $this->expectException(AuthenticationException::class);
        $user->isLoggedIn();
    }

    public function testUnparsableLogoutDateIsRejected(): void
    {
        $response = new stdClass();
        $response->DateLogout = 'nonsense';

        $user = new User($this->createWsdlManager($response));
        $user->setLoginData('token');

        $this->expectException(UnexpectedValueException::class);
        $user->updateLogoutTime();
    }

    public function testResetLoginData(): void
    {
        $user = new User($this->createWsdlManager());
        $user->setLoginData('token', 33, 100, new DateTimeImmutable());

        $user->resetLoginData();

        self::assertNull($user->getLoginId());
        self::assertNull($user->getRoleId());
        self::assertNull($user->getUnitId());
        self::assertNull($user->getLogoutDate());
    }

    public function testNotLoggedInWithoutLoginId(): void
    {
        $user = new User($this->createWsdlManager());

        self::assertNull($user->getLoginId());
        self::assertFalse($user->isLoggedIn());
        self::assertFalse($user->isLoggedIn(true));
        self::assertFalse($user->updateLogoutTime());
    }

    /**
     * @return WsdlManager&MockInterface
     */
    private function createWsdlManager(stdClass|Throwable|null $loginUpdateRefreshResult = null): WsdlManager
    {
        $webService = Mockery::mock(WebServiceInterface::class);
        $expectation = $webService->shouldReceive('call')->with('LoginUpdateRefresh', [['ID' => 'token']]);
        if ($loginUpdateRefreshResult instanceof Throwable) {
            $expectation->andThrow($loginUpdateRefreshResult);
        } else {
            $expectation->andReturn($loginUpdateRefreshResult);
        }

        $wsdlManager = Mockery::mock(WsdlManager::class);
        $wsdlManager->shouldReceive('getWebService')->with(WebServiceName::USER_MANAGEMENT, 'token')->andReturn($webService);

        return $wsdlManager;
    }
}
