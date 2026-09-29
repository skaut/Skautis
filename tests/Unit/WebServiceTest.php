<?php

declare(strict_types=1);

namespace Skaut\Skautis\Test\Unit;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Skaut\Skautis\User;
use Skaut\Skautis\Wsdl\AuthenticationException;
use Skaut\Skautis\Wsdl\Event\RequestFailEvent;
use Skaut\Skautis\Wsdl\Event\RequestPostEvent;
use Skaut\Skautis\Wsdl\Event\RequestPreEvent;
use Skaut\Skautis\Wsdl\PermissionException;
use Skaut\Skautis\Wsdl\WebService;
use Skaut\Skautis\Wsdl\WsdlException;
use SoapClient;
use SoapFault;
use stdClass;

final class WebServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private const array INIT = ['ID_Application' => 'app', User::ID_LOGIN => 'token'];

    public function testArgumentsAreWrappedAndMergedWithDefaults(): void
    {
        $client = Mockery::mock(SoapClient::class);
        $client->shouldReceive('__soapCall')
            ->once()
            ->withArgs(static fn (string $name, array $args): bool => $name === 'UnitDetail'
                && $args === [['unitDetailInput' => ['ID_Application' => 'app', User::ID_LOGIN => 'token', 'ID' => 1]]])
            ->andReturn($this->singleRecord('UnitDetail'));

        $service = new WebService($client, self::INIT);

        self::assertInstanceOf(stdClass::class, $service->call('UnitDetail', [['ID' => 1]]));
    }

    public function testCustomInputWrapper(): void
    {
        $client = Mockery::mock(SoapClient::class);
        $client->shouldReceive('__soapCall')
            ->once()
            ->withArgs(static fn (string $name, array $args): bool => $args === [['eventGeneral' => ['ID_Application' => 'app', User::ID_LOGIN => 'token', 'ID' => 1]]])
            ->andReturn($this->singleRecord('EventGeneralInsert'));

        $service = new WebService($client, self::INIT);

        $service->call('EventGeneralInsert', [['ID' => 1], 'eventGeneral']);
    }

    public function testEventsAreDispatchedAroundSuccessfulRequest(): void
    {
        $client = Mockery::mock(SoapClient::class);
        $client->shouldReceive('__soapCall')->once()->andReturn($this->singleRecord('UnitDetail'));

        $dispatcher = Mockery::mock(EventDispatcherInterface::class);
        $dispatcher->shouldReceive('dispatch')->once()->ordered()->withArgs(
            static fn (object $event): bool => $event instanceof RequestPreEvent && $event->getFname() === 'UnitDetail',
        );
        $dispatcher->shouldReceive('dispatch')->once()->ordered()->withArgs(
            static fn (object $event): bool => $event instanceof RequestPostEvent && $event->getResult() instanceof stdClass && $event->getTrace() !== [],
        );

        $service = new WebService($client, self::INIT, $dispatcher);
        $service->call('UnitDetail', [['ID' => 1]]);
    }

    public function testFailedRequestDispatchesFailEventAndThrows(): void
    {
        $client = Mockery::mock(SoapClient::class);
        $client->shouldReceive('__soapCall')->once()->andThrow(new SoapFault('Server', 'Nemáte oprávnění k akci OU_Person_ALL nad záznamem ID=1!'));

        $dispatcher = Mockery::mock(EventDispatcherInterface::class);
        $dispatcher->shouldReceive('dispatch')->once()->ordered()->withArgs(static fn (object $event): bool => $event instanceof RequestPreEvent);
        $dispatcher->shouldReceive('dispatch')->once()->ordered()->withArgs(
            static fn (object $event): bool => $event instanceof RequestFailEvent && $event->getExceptionClass() === SoapFault::class,
        );

        $service = new WebService($client, self::INIT, $dispatcher);

        $this->expectException(PermissionException::class);
        $service->call('PersonAll', [['ID_Unit' => 1]]);
    }

    /**
     * @param class-string<WsdlException> $expectedException
     */
    #[DataProvider('provideFaults')]
    public function testFaultsAreTranslated(string $message, string $expectedException): void
    {
        $client = Mockery::mock(SoapClient::class);
        $client->shouldReceive('__soapCall')->once()->andThrow(new SoapFault('Server', $message));

        $service = new WebService($client, self::INIT);

        try {
            $service->call('UnitDetail', [['ID' => 1]]);
            self::fail('Exception expected');
        } catch (WsdlException $exception) {
            self::assertSame($expectedException, $exception::class);
            self::assertSame($message, $exception->getMessage());
            self::assertInstanceOf(SoapFault::class, $exception->getPrevious());
        }
    }

    /**
     * @return iterable<string, array{string, class-string<WsdlException>}>
     */
    public static function provideFaults(): iterable
    {
        yield 'logged out' => ['Uživatel byl odhlášen', AuthenticationException::class];
        yield 'login expired' => ['Přihlášení vypršelo.', AuthenticationException::class];
        yield 'login does not exist' => ['Přihlášení neexistuje.', AuthenticationException::class];
        yield 'not logged in' => ['Uživatel není přihlášen.', AuthenticationException::class];
        yield 'no permission' => ['Nemáte oprávnění k akci OU_PersonContact_ALL_Person nad záznamem ID=1!', PermissionException::class];
        yield 'role has no permission' => ['Role nemá oprávnění k akci.', PermissionException::class];
        yield 'insufficient rights' => ['Nedostatečná práva.', PermissionException::class];
        yield 'not allowed' => ['Toto není povoleno.', PermissionException::class];
        yield 'anything else' => ['Chyba validace (Participant_PersonIsAllreadyParticipantGeneral)', WsdlException::class];
    }

    public function testUnexpectedResponseIsRejected(): void
    {
        $client = Mockery::mock(SoapClient::class);
        $client->shouldReceive('__soapCall')->once()->andReturn('garbage');

        $service = new WebService($client, self::INIT);

        $this->expectException(WsdlException::class);
        $service->call('UnitDetail', [['ID' => 1]]);
    }

    private function singleRecord(string $method): stdClass
    {
        $record = new stdClass();
        $record->ID = 1;

        $response = new stdClass();
        $response->{$method.'Result'} = $record;

        return $response;
    }
}
