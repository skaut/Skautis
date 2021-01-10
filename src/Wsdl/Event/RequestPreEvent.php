<?php
declare(strict_types = 1);

namespace Skaut\Skautis\Wsdl\Event;

use Serializable;

class RequestPreEvent implements Serializable
{

    /**
     * @var string Nazev funkce volane pomoci SOAP requestu
     */
    private $fname;

    /**
     * Parametry SOAP requestu na server
     *
     * @var array<int|string, mixed>
     */
    private $args;

    /**
     * @var array<string, mixed>
     */
    private $options;

    /**
     * @var array<int, string>
     */
    private $inputHeaders;

    /**
     * @var array<int, array<string, mixed>> Zasobnik volanych funkci
     */
    private $trace;


    /**
     * @param string $fname Nazev volane funkce
     * @param array<int|string, mixed> $args  Argumenty pozadavku
     * @param array<string, mixed> $options
     * @param array<int, string> $inputHeaders
     * @param array<int, array<string, mixed>> $trace Zasobnik volanych funkci
     */
    public function __construct(
      string $fname,
      array $args,
      array $options,
      array $inputHeaders,
      array $trace
    ) {
        $this->fname = $fname;
        $this->args = $args;
        $this->options = $options;
        $this->inputHeaders = $inputHeaders;
        $this->trace = $trace;
    }

    public function serialize(): string
    {
        $data = [
            'fname' => $this->fname,
            'args' => $this->args,
            'options' => $this->options,
            'inputHeaders' => $this->inputHeaders,
            'trace' => $this->trace,
        ];
        return serialize($data);
    }

    /**
     * @param string $data
     */
    public function unserialize($data): void
    {
        $data = unserialize($data, ['allowed_classes' => [self::class]]);
        $this->fname = (string) $data['fname'];
        $this->args = (array) $data['args'];
        $this->options = (array) $data['options'];
        $this->inputHeaders = (array) $data['inputHeaders'];
        $this->trace = (array) $data['trace'];
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
     * @return array<int, string>
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
