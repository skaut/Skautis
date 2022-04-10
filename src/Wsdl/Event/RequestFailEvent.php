<?php
declare(strict_types = 1);

namespace Skaut\Skautis\Wsdl\Event;

use Serializable;
use Throwable;

class RequestFailEvent implements Serializable
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
     * @var float Pocet sekund trvani pozadvku
     */
    private $time;

    /**
     * Ne vsechny exceptions jdou serializovat.
     * Po unserializaci je null.
     *
     * @var Throwable|null
     */
    private $throwable;

    /**
     * @var string
     */
    private $exceptionClass;

    /**
     * Pouziva __toString() methodu
     *
     * @var string
     */
    private $exceptionString;

    /**
     * @param string $fname Nazev volane funkce
     * @param array<int|string, mixed> $args  Argumenty pozadavku
     */
    public function __construct(
      string $fname,
      array $args,
      Throwable $throwable,
      float $duration
    ) {
        $this->fname = $fname;
        $this->args = $args;
        $this->throwable = $throwable;
        $this->exceptionClass = get_class($throwable);
        $this->exceptionString = (string) $throwable;
        $this->time = $duration;
    }

    /**
     * @return array<mixed>
     */
    public function __serialize(): array {
        return [
            'fname' => $this->fname,
            'args' => $this->args,
            'time' => $this->time,
            'exception_class' =>  $this->exceptionClass,
            'exception_string' => $this->exceptionString,
        ];
    }

    public function serialize(): string
    {
        return serialize($this->__serialize());
    }

    /**
     * @param array<mixed> $data
     */
    public function __unserialize(array $data): void {
        $this->fname = (string) $data['fname'];
        $this->args = (array) $data['args'];
        $this->time = (float) $data['time'];
        $this->exceptionClass = (string) $data['exception_class'];
        $this->exceptionString = (string) $data['exception_string'];
    }

    /**
	 * @param string $data
	 */
    public function unserialize($data): void
    {
        $data = unserialize($data, ['allowed_classes' => [self::class]]);
        $this->__unserialize($data);
    }

    /**
     * Vrati tridu exception
     *
     * Pouziva se tato metoda protoze SoapFault exception vyhozena SoapClientem nejde serializovat
     */
    public function getExceptionClass(): string
    {
        if ($this->throwable === null) {
            return $this->exceptionClass;
        }

        return get_class($this->throwable);
    }

    /**
     * Vrati textovou podobu exception
     */
    public function getExceptionString(): string
    {
        if ($this->throwable === null) {
            return $this->exceptionString;
        }

        return (string)$this->throwable;
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
     * Pocet sekund trvani pozadvku
     */
    public function getDuration(): float
    {
      return $this->time;
    }

    /**
     * @return Throwable|null null when object is de-serialized
     *
     * @see RequestFailEvent::getExceptionString()
     * @see RequestFailEvent::getExceptionClass()
     */
    public function getThrowable(): ?Throwable
    {
      return $this->throwable;
    }

}
