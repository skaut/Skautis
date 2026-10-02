<?php

declare(strict_types=1);

namespace Skaut\Skautis\Test\Unit\Wsdl\Event;

use PHPUnit\Framework\TestCase;
use Skaut\Skautis\UnexpectedValueException;
use Skaut\Skautis\Wsdl\Event\RequestPostEvent;
use stdClass;

final class RequestPostEventTest extends TestCase
{
    public function testSerialization(): void
    {
        $record = new stdClass();
        $record->a = 'b';

        $event = new RequestPostEvent('asd', [['argument' => 'value']], [$record], 11.11, debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS));

        $unserialized = unserialize(serialize($event));

        self::assertInstanceOf(RequestPostEvent::class, $unserialized);
        self::assertSame('asd', $unserialized->getFname());
        self::assertSame([['argument' => 'value']], $unserialized->getArgs());
        self::assertSame(11.11, $unserialized->getDuration());
        self::assertNotEmpty($unserialized->getTrace());

        $result = $unserialized->getResult();
        self::assertIsArray($result);
        self::assertInstanceOf(stdClass::class, $result[0]);
        self::assertSame('b', $result[0]->a);
    }

    public function testSingleObjectAndNullResultsKeepTheirType(): void
    {
        $record = new stdClass();
        $record->a = 'b';

        $single = unserialize(serialize(new RequestPostEvent('asd', [], $record, 1.0, [])));
        $missing = unserialize(serialize(new RequestPostEvent('asd', [], null, 1.0, [])));

        self::assertInstanceOf(RequestPostEvent::class, $single);
        self::assertInstanceOf(stdClass::class, $single->getResult());
        self::assertInstanceOf(RequestPostEvent::class, $missing);
        self::assertNull($missing->getResult());
    }

    public function testEventSerializedBy30CanBeUnserialized(): void
    {
        // 3.0 stored the duration under 'time'
        $legacy = 'O:41:"Skaut\Skautis\Wsdl\Event\RequestPostEvent":5:{s:5:"fname";s:3:"asd";s:4:"args";a:0:{}s:4:"time";d:11.11;s:6:"result";a:0:{}s:5:"trace";a:0:{}}';

        $unserialized = unserialize($legacy);

        self::assertInstanceOf(RequestPostEvent::class, $unserialized);
        self::assertSame('asd', $unserialized->getFname());
        self::assertSame(11.11, $unserialized->getDuration());
        self::assertSame([], $unserialized->getResult());
    }

    public function testPayloadWithoutDurationIsRejected(): void
    {
        $broken = 'O:41:"Skaut\Skautis\Wsdl\Event\RequestPostEvent":4:{s:5:"fname";s:3:"asd";s:4:"args";a:0:{}s:6:"result";a:0:{}s:5:"trace";a:0:{}}';

        $this->expectException(UnexpectedValueException::class);
        unserialize($broken);
    }
}
