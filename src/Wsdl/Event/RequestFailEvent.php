<?php

declare(strict_types=1);

namespace Skaut\Skautis\Wsdl\Event;

use Throwable;

/**
 * Dispatched when a SOAP request fails.
 *
 * SoapFault cannot be serialized, so the exception class and its string form are kept separately;
 * after unserialization getThrowable() returns null.
 */
final class RequestFailEvent
{
    private ?Throwable $throwable;
    private string $exceptionClass;
    private string $exceptionString;

    /**
     * @param string                           $fname    skautIS method name
     * @param array<int|string, mixed>         $args     arguments as sent to SoapClient
     * @param float                            $duration seconds
     * @param array<int, array<string, mixed>> $trace    debug_backtrace() of the call
     */
    public function __construct(
        private string $fname,
        private array $args,
        Throwable $throwable,
        private float $duration,
        private array $trace,
    ) {
        $this->throwable = $throwable;
        $this->exceptionClass = $throwable::class;
        $this->exceptionString = (string) $throwable;
    }

    /**
     * @return array<string, mixed>
     */
    public function __serialize(): array
    {
        return [
            'fname' => $this->fname,
            'args' => $this->args,
            'duration' => $this->duration,
            'exception_class' => $this->exceptionClass,
            'exception_string' => $this->exceptionString,
            'trace' => $this->trace,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function __unserialize(array $data): void
    {
        /** @var array{fname: string, args: array<int|string, mixed>, duration: float, exception_class: string, exception_string: string, trace: array<int, array<string, mixed>>} $data */
        $this->fname = $data['fname'];
        $this->args = $data['args'];
        $this->duration = $data['duration'];
        $this->throwable = null;
        $this->exceptionClass = $data['exception_class'];
        $this->exceptionString = $data['exception_string'];
        $this->trace = $data['trace'];
    }

    public function getExceptionClass(): string
    {
        return $this->exceptionClass;
    }

    public function getExceptionString(): string
    {
        return $this->exceptionString;
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
     * @return Throwable|null null after unserialization
     */
    public function getThrowable(): ?Throwable
    {
        return $this->throwable;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTrace(): array
    {
        return $this->trace;
    }
}
