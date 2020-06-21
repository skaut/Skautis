<?php
declare(strict_types = 1);

namespace Skaut\Skautis\Wsdl\Event;

use Serializable;
use stdClass;

class RequestPostEvent implements Serializable
{

    /**
     * @var string Nazev funkce volane pomoci SOAP requestu
     */
    private $fname;

    /**
     * Parametry SOAP requestu na server
     *
     * @var array<int, mixed>
     */
    private $args;


    /**
     * @var float Pocet sekund trvani pozadvku
     */
    private $time;

	  /**
     * @var array<int|string, mixed>|\stdClass|null
     */
    private $result;

    /**
     * @param string $fname Nazev volane funkce
     * @param array<int, mixed> $args  Argumenty pozadavku
     * @param array<int|string, mixed>|stdClass|null $result
     */
    public function __construct(
      string $fname,
      array $args,
      $result,
      float $duration
    ) {
        $this->fname = $fname;
        $this->args = $args;
        $this->result = $result;
        $this->time = $duration;
    }

    public function serialize(): string
    {
        $data = [
            'fname' => $this->fname,
            'args' => $this->args,
            'time' => $this->time,
            'result' => $this->result,
        ];
        return serialize($data);
    }

    /**
     * @param string $data
     */
    public function unserialize($data): void
    {
        $data = unserialize($data, ['allowed_classes' => [self::class, stdClass::class]]);
        $this->fname = (string) $data['fname'];
        $this->args = (array) $data['args'];
        $this->time = (float) $data['time'];
        $this->result = (array) $data['result'];
    }

    public function getFname(): string
    {
      return $this->fname;
    }

    /**
     * @return array<int, mixed>
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
     * @return array<int|string, mixed>|\stdClass|null
     */
    public function getResult()
    {
      return $this->result;
    }

}
