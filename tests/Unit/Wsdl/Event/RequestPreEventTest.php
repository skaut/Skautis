<?php

declare(strict_types=1);

namespace Skaut\Skautis\Test\Unit\Wsdl\Event;

use PHPUnit\Framework\TestCase;
use Skaut\Skautis\Wsdl\Event\RequestPreEvent;

final class RequestPreEventTest extends TestCase
{
    public function testSerialization(): void
    {
        $event = new RequestPreEvent(
            'asd',
            [['argument' => 'value']],
            ['option' => 'value'],
            ['header'],
            debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS),
        );

        $unserialized = unserialize(serialize($event));

        self::assertInstanceOf(RequestPreEvent::class, $unserialized);
        self::assertSame('asd', $unserialized->getFname());
        self::assertSame([['argument' => 'value']], $unserialized->getArgs());
        self::assertSame(['option' => 'value'], $unserialized->getOptions());
        self::assertSame(['header'], $unserialized->getInputHeaders());
        self::assertNotEmpty($unserialized->getTrace());
    }
}
