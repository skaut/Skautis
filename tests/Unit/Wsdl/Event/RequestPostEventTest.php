<?php

declare(strict_types=1);


namespace Skaut\Skautis\Test\Unit\Wsdl\Event;


use PHPUnit\Framework\TestCase;
use Skaut\Skautis\Wsdl\Event\RequestPostEvent;

class RequestPostEventTest extends TestCase
{

    public function testDeserialize(): void
    {
        $args = [
          [
            'argument' => 'value',
          ],
        ];
        $result = [(object)['a' => 'b']];
        $duration = 11.11;
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $event = new RequestPostEvent('asd', $args, $result, $duration, $trace);

        $serialized = serialize($event);
        /** @var RequestPostEvent $unserialized */
        $unserialized = unserialize($serialized);

        $this->assertSame('asd', $unserialized->getFname());
        $this->assertArrayHasKey(0, $unserialized->getArgs());
        $this->assertArrayHasKey('argument', $unserialized->getArgs()[0]);
        $this->assertArrayHasKey(0, $unserialized->getResult());
        $this->assertObjectHasAttribute('a', $unserialized->getResult()[0]);
        $this->assertSame('b', $unserialized->getResult()[0]->a);
        $this->assertSame('value', $unserialized->getArgs()[0]['argument']);
        $this->assertSame(11.11, $unserialized->getDuration());
    }
}
