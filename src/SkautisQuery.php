<?php

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
     * @var int Doba trvani pozadvku
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
    protected $exceptionClass = "";

    /**
     * Po unserializaci je zde text exxception
     *
     * Pouziva __toString() methodu
     *
     * @var string
     */
    protected $exceptionString = "";

    /**
     *
     *
     * @param string $fname Nazev volane funkce
     * @param array  $args  Argumenty pozadavku
     * @param string $trace Zasobnik volanych funkci
     */
    public function __construct($fname, array $args = [], array $trace = [])
    {
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
            'exception_class' => is_null($this->exception) ? "" : get_class($this->exception),
            'exception_string' => is_null($this->exception) ? "" : (string)$this->exception,
        ];
        return serialize($data);
    }

    public function unserialize($data)
    {
        $data = unserialize($data);
        $this->fname = $data['fname'];
        $this->args = $data['args'];
        $this->trace = $data['trace'];
        $this->time = $data['time'];
        $this->result = $data['result'];
        $this->exceptionClass = $data['exception_class'];
        $this->exceptionString = $data['exception_string'];
    }

    /**
     * Oznac pozadavek za dokonceny a uloz vysledek
     *
     * @param mixed $result Odpoved ze serveru
     * @param \Exception Výjimka v pripade problemu
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
    public function getExceptionClass()
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
    public function getExceptionString()
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
    public function hasFailed()
    {
        return $this->exception !== null || strlen($this->exceptionClass) > 0;
    }
}
