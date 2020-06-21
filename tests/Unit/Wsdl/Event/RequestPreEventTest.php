<?php

declare(strict_types=1);


namespace Skaut\Skautis\Test\Unit\Wsdl\Event;


use PHPUnit\Framework\TestCase;
use Skaut\Skautis\Wsdl\Event\RequestPreEvent;

class RequestPreEventTest extends TestCase
{


    public function testDeserialize(): void
    {
        $args = [
            [
                'argument' => 'value',
            ],
        ];

        $options = [
            'option' => 'value'
        ];

        $headers = [
            'header' => 'value'
        ];

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $event = new RequestPreEvent('asd', $args, $options, $headers, $trace);

        $serialized = serialize($event);
        /** @var RequestPreEvent $unserialized */
        $unserialized = unserialize($serialized);

        $this->assertSame('asd', $unserialized->getFname());
        $this->assertArrayHasKey(0, $unserialized->getArgs());
        $this->assertArrayHasKey('argument', $unserialized->getArgs()[0]);
        $this->assertSame('value', $unserialized->getArgs()[0]['argument']);
        $this->assertArrayHasKey('option', $unserialized->getOptions());
        $this->assertSame('value', $unserialized->getOptions()['option']);
        $this->assertArrayHasKey('header', $unserialized->getInputHeaders());
        $this->assertSame('value', $unserialized->getInputHeaders()['header']);
    }
}
