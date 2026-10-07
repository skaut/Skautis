<?php

declare(strict_types=1);

namespace Skaut\Skautis\Wsdl\Event;

use Skaut\Skautis\UnexpectedValueException;
use stdClass;

/**
 * Dispatched after a successful SOAP request.
 */
final class RequestPostEvent
{
    /**
     * @param string                                                       $fname    skautIS method name
     * @param array<int|string, mixed>                                     $args     arguments as sent to SoapClient
     * @param array<int|string, mixed>|stdClass|bool|int|float|string|null $result   parsed response
     * @param float                                                        $duration seconds
     * @param array<int, array<string, mixed>>                             $trace    debug_backtrace() of the call
     */
    public function __construct(
        private string $fname,
        private array $args,
        private array|stdClass|bool|int|float|string|null $result,
        private float $duration,
        private array $trace,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function __serialize(): array
    {
        return [
            'fname' => $this->fname,
            'args' => $this->args,
            'result' => $this->result,
            'duration' => $this->duration,
            'trace' => $this->trace,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function __unserialize(array $data): void
    {
        /** @var array{fname: string, args: array<int|string, mixed>, result: array<int|string, mixed>|stdClass|bool|int|float|string|null, duration?: float, time?: float, trace: array<int, array<string, mixed>>} $data */
        $this->fname = $data['fname'];
        $this->args = $data['args'];
        $this->result = $data['result'];
        // 3.0 stored the duration under 'time'
        $this->duration = $data['duration'] ?? $data['time'] ?? throw new UnexpectedValueException('Serialized event has no duration.');
        $this->trace = $data['trace'];
    }

    public function getFname(): string
    {
        return $this->fname;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * Seconds the request took.
     */
    public function getDuration(): float
    {
        return $this->duration;
    }

    /**
     * @return array<int|string, mixed>|stdClass|bool|int|float|string|null
     */
    public function getResult(): array|stdClass|bool|int|float|string|null
    {
        return $this->result;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTrace(): array
    {
        return $this->trace;
    }
}
