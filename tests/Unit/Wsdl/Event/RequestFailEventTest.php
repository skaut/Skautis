<?php

declare(strict_types=1);

namespace Skaut\Skautis\Test\Unit\Wsdl\Event;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Skaut\Skautis\Wsdl\Event\RequestFailEvent;
use SoapFault;

final class RequestFailEventTest extends TestCase
{
    public function testExceptionDetails(): void
    {
        $throwable = new RuntimeException('my message');

        $event = new RequestFailEvent('asd', [], $throwable, 30, []);

        self::assertSame($throwable, $event->getThrowable());
        self::assertStringContainsString('my message', $event->getExceptionString());
        self::assertSame(RuntimeException::class, $event->getExceptionClass());
    }

    public function testSerializationKeepsDetailsWithoutTheThrowable(): void
    {
        $event = new RequestFailEvent(
            'asd',
            [['argument' => 'value']],
            new SoapFault('code-is-string', 'fault-string'),
            30.22,
            debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS),
        );

        $unserialized = unserialize(serialize(unserialize(serialize($event))));

        self::assertInstanceOf(RequestFailEvent::class, $unserialized);
        self::assertSame('asd', $unserialized->getFname());
        self::assertSame(30.22, $unserialized->getDuration());
        self::assertSame([['argument' => 'value']], $unserialized->getArgs());
        self::assertNull($unserialized->getThrowable());
        self::assertStringContainsString('code-is-string', $unserialized->getExceptionString());
        self::assertStringContainsString('fault-string', $unserialized->getExceptionString());
        self::assertSame(SoapFault::class, $unserialized->getExceptionClass());
        self::assertNotEmpty($unserialized->getTrace());
    }
}
