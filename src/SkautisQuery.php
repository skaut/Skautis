<?php
declare(strict_types = 1);

namespace Skautis;

/**
 * Trida slouzici pro debugovani SOAP pozadvku na servery Skautisu
 */
class SkautisQuery implements \Serializable
{

    /**
     * @var string Nazev funkce volane pomoci SOAP requestu
     */
    public $fname;

    /**
     * @var array Parametry SOAP requestu na server
     */
    public $args;

    /**
     * @var array Zasobnik volanych funkci
     */
    public $trace;

    /**
     * @var float Doba trvani pozadvku
     */
    public $time;

    public $result;

    /**
     * V pripade ze SOAP pozadavek selze
     *
     * Nelze povolit uzivateli primy pristup kvuli serializaci. Ne vsechny exceptions jdou serializovat.
     *
     * @var \Exception|null
     */
    protected $exception = null;

    /**
     * Po unserializaci Query s exception je zde jeji trida
     *
     * @var string
     */
    protected $exceptionClass = '';

    /**
     * Po unserializaci je zde text exxception
     *
     * Pouziva __toString() methodu
     *
     * @var string
     */
    protected $exceptionString = '';

    /**
     *
     *
     * @param string $fname Nazev volane funkce
     * @param array  $args  Argumenty pozadavku
     * @param array $trace Zasobnik volanych funkci
     */
    public function __construct(
      string $fname,
      array $args = [],
      array $trace = []
    ) {
        $this->fname = $fname;
        $this->args = $args;
        $this->trace = $trace;
        $this->time = -microtime(true);
    }

    public function serialize()
    {
        $data = [
            'fname' => $this->fname,
            'args' => $this->args,
            'trace' => $this->trace,
            'time' => $this->time,
            'result' => $this->result,
            'exception_class' => $this->exception === null ? '' : get_class($this->exception),
            'exception_string' => $this->exception === null ? '' : (string)$this->exception,
        ];
        return serialize($data);
    }

    public function unserialize($data)
    {
        $data = unserialize($data);
        $this->fname = (string) $data['fname'];
        $this->args = (array) $data['args'];
        $this->trace = (array) $data['trace'];
        $this->time = (float) $data['time'];
        $this->result = $data['result'];
        $this->exceptionClass = (string) $data['exception_class'];
        $this->exceptionString = (string) $data['exception_string'];
    }

    /**
     * Oznac pozadavek za dokonceny a uloz vysledek
     *
     * @param mixed $result Odpoved ze serveru
     * @param \Exception $e Výjimka v pripade problemu
     */
    public function done($result = null, \Exception $e = null)
    {
        $this->time += microtime(true);
        $this->result = $result;
        $this->exception = $e;

        return $this;
    }

    /**
     * Vrati tridu exception
     *
     * Pouziva se tato metoda protoze SoapFault exception vyhozena SoapClientem nejde serializovat
     *
     * @return string
     */
    public function getExceptionClass(): string
    {
        if ($this->exception === null) {
            return $this->exceptionClass;
        }

        return get_class($this->exception);
    }

    /**
     * Vrati textovou podobu exception
     *
     * @return string
     */
    public function getExceptionString(): string
    {
        if ($this->exception === null) {
            return $this->exceptionString;
        }

        return (string)$this->exception;
    }

    /**
     * Kontrola jestli se pozadavek zdaril
     *
     * @return bool
     */
    public function hasFailed(): bool
    {
        return $this->exception !== null || strlen($this->exceptionClass) > 0;
    }
}
