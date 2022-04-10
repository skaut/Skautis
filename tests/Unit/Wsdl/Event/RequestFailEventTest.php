<?php

declare(strict_types=1);


namespace Skaut\Skautis\Test\Unit\Wsdl\Event;


use PHPUnit\Framework\TestCase;
use RuntimeException;
use Skaut\Skautis\Wsdl\Event\RequestFailEvent;
use SoapFault;

class RequestFailEventTest extends TestCase
{

    public function testExceptionMessage(): void
    {
        $throwable = new RuntimeException('my message');
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $event = new RequestFailEvent('asd', [], $throwable, 30, $trace);
        $this->assertStringContainsString('my message', $event->getExceptionString());
        $this->assertSame(RuntimeException::class, $event->getExceptionClass());
    }

    public function testDeserialization(): void
    {
        $throwable = new SoapFault('code-is-string', 'fault-string');
        $args = [
            [
                'argument' => 'value',
            ],
        ];
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $event = new RequestFailEvent('asd', $args, $throwable, 30.22, $trace);

        $serialized = serialize($event);
        /** @var RequestFailEvent $unserialized */
        $unserialized = unserialize($serialized);

        $this->assertSame('asd', $unserialized->getFname());
        $this->assertSame(30.22, $unserialized->getDuration());
        $this->assertArrayHasKey(0, $unserialized->getArgs());
        $this->assertArrayHasKey('argument', $unserialized->getArgs()[0]);
        $this->assertSame('value', $unserialized->getArgs()[0]['argument']);
        $this->assertStringContainsString('code-is-string', $unserialized->getExceptionString());
        $this->assertStringContainsString('fault-string', $unserialized->getExceptionString());
        $this->assertSame(SoapFault::class, $unserialized->getExceptionClass());
    }

    public function testRepeatedSerializationDeserialization(): void
    {
        $throwable = new SoapFault('code-is-string', 'fault-string');
        $args = [
            [
                'argument' => 'value',
            ],
        ];
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $event = new RequestFailEvent('asd', $args, $throwable, 30.22, $trace);

        $serialized = serialize($event);
        $unserialized = unserialize($serialized);
        $serialized = serialize($unserialized);
        $unserialized = unserialize($serialized);

        $this->assertSame('asd', $unserialized->getFname());
        $this->assertSame(30.22, $unserialized->getDuration());
        $this->assertArrayHasKey(0, $unserialized->getArgs());
        $this->assertArrayHasKey('argument', $unserialized->getArgs()[0]);
        $this->assertSame('value', $unserialized->getArgs()[0]['argument']);
        $this->assertStringContainsString('code-is-string', $unserialized->getExceptionString());
        $this->assertStringContainsString('fault-string', $unserialized->getExceptionString());
        $this->assertSame(SoapFault::class, $unserialized->getExceptionClass());
    }
}
