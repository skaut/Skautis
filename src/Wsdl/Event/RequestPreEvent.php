<?php

declare(strict_types=1);

namespace Skaut\Skautis\Wsdl\Event;

/**
 * Dispatched before a SOAP request is sent.
 */
final class RequestPreEvent
{
    /**
     * @param string                           $fname        skautIS method name
     * @param array<int|string, mixed>         $args         arguments as sent to SoapClient
     * @param array<string, mixed>             $options
     * @param array<int, mixed>                $inputHeaders
     * @param array<int, array<string, mixed>> $trace        debug_backtrace() of the call
     */
    public function __construct(
        private string $fname,
        private array $args,
        private array $options,
        private array $inputHeaders,
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
            'options' => $this->options,
            'inputHeaders' => $this->inputHeaders,
            'trace' => $this->trace,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function __unserialize(array $data): void
    {
        /** @var array{fname: string, args: array<int|string, mixed>, options: array<string, mixed>, inputHeaders: array<int, mixed>, trace: array<int, array<string, mixed>>} $data */
        $this->fname = $data['fname'];
        $this->args = $data['args'];
        $this->options = $data['options'];
        $this->inputHeaders = $data['inputHeaders'];
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
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return array<int, mixed>
     */
    public function getInputHeaders(): array
    {
        return $this->inputHeaders;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getTrace(): array
    {
        return $this->trace;
    }
}
