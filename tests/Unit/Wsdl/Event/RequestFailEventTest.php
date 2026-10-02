<?php

declare(strict_types=1);

namespace Skaut\Skautis\Test\Unit\Wsdl\Event;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Skaut\Skautis\UnexpectedValueException;
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

    public function testEventSerializedBy30CanBeUnserialized(): void
    {
        // 3.0 stored the duration under 'time'
        $legacy = 'O:41:"Skaut\Skautis\Wsdl\Event\RequestFailEvent":6:{s:5:"fname";s:3:"asd";s:4:"args";a:0:{}s:4:"time";d:30.22;s:15:"exception_class";s:9:"SoapFault";s:16:"exception_string";s:12:"fault-string";s:5:"trace";a:0:{}}';

        $unserialized = unserialize($legacy);

        self::assertInstanceOf(RequestFailEvent::class, $unserialized);
        self::assertSame('asd', $unserialized->getFname());
        self::assertSame(30.22, $unserialized->getDuration());
        self::assertSame(SoapFault::class, $unserialized->getExceptionClass());
        self::assertSame('fault-string', $unserialized->getExceptionString());
    }

    public function testPayloadWithoutDurationIsRejected(): void
    {
        $broken = 'O:41:"Skaut\Skautis\Wsdl\Event\RequestFailEvent":5:{s:5:"fname";s:3:"asd";s:4:"args";a:0:{}s:15:"exception_class";s:9:"SoapFault";s:16:"exception_string";s:12:"fault-string";s:5:"trace";a:0:{}}';

        $this->expectException(UnexpectedValueException::class);
        unserialize($broken);
    }
}
