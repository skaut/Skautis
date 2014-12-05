<?php

namespace Skautis;

/**
 * Trida slouzici pro debugovani SOAP pozadvku na servery Skautisu
 */
class SkautisQuery
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
     * @var \Exception|NULL
     */
    public $exception = NULL;

    /**
     *
     *
     * @param string $fname Nazev volane funkce
     * @param array  $args  Argumenty pozadavku
     * @param string $trace Zasobnik volanych funkci
     */
    public function __construct($fname, array $args = array(), array $trace = array()) {
        $this->fname = $fname;
        $this->args = $args;
        $this->trace = $trace;
        $this->time = -microtime(TRUE);
    }


    /**
     * Oznac pozadavek za dokonceny a uloz vysledek
     *
     * @param mixed $result Odpoved serveru na pozadavek TODO specifikovat typ
     */
    public function done($result = NULL) {
        $this->result = $result;
        $this->time += microtime(TRUE);
        return $this;
    }

    /**
     * Kontrola jestli se pozadavek zdaril
     *
     * @return bool
     */
    public function hasFailed() {
	return $this->exception !== NULL;
    }
}
