<?php

namespace Skautis;

use Skautis\EventDispatcher\EventDispatcherInterface;
use Skautis\EventDispatcher\EventDispatcherTrait;
use Skautis\Exception\AuthenticationException;
use Skautis\Exception\AbortException;
use Skautis\Exception\WsdlException;
use Skautis\Exception\PermissionException;
use Skautis\SkautisQuery;
use SoapFault;
use stdClass;
use SoapClient;

/**
 * @author Hána František <sinacek@gmail.com>
 */
class WS extends SoapClient implements EventDispatcherInterface
{

    use EventDispatcherTrait;

    const EVENT_ALL = -1;
    const EVENT_SUCCESS = 1;
    const EVENT_FAILURE = 2;

    /**
     * základní údaje volané při každém požadavku
     * ID_Application, ID_Login
     * @var array
     */
    protected $init;

    /**
     * Indikuje jestli ma ukladat informace pro debugovani
     *
     * @var bool
     */
    public $profiler;

    /**
     * @param mixed $wdl Odkaz na WSDL soubor
     * @param array $init Zakladni informace pro vsechny pozadavky
     * @param bool $compression Ma pouzivat kompresi na prenasena data?
     * @param bool $profiler Ma uklada data pro profilovani?
     */
    public function __construct($wsdl, array $soapOpts, $profiler = FALSE) {
        $this->init = $soapOpts;
        $this->profiler = $profiler;
        if (empty($wsdl)) {
            throw new AbortException("WSDL musí být nastaven");
        }
        parent::__construct($wsdl, $soapOpts);
    }

    /**
     * Magicka metoda starjici se spravne volani SOAP metod
     */
    public function __call($function_name, $arguments) {

        return $this->__soapCall($function_name, $arguments);
    }

    /**
     * Metoda provadejici SOAP pozadavek na servery Skautisu
     *
     * @param string $function_name
     * @param array $arguments ([0]=args [1]=cover)
     *
     * @return type
     */
    public function __soapCall($function_name, $arguments, $options = null, $input_headers = null, &$output_headers = null) {
        //public function __call($function_name, $arguments) {
        $fname = ucfirst($function_name);

        if (!isset($arguments[0]) || !is_array($arguments[0])) {
            $arguments[0] = array();
        }

        $args = array_merge($this->init, $arguments[0]); //k argumentum připoji vlastni informace o aplikaci a uzivateli
        //cover
        if (isset($arguments[1]) && $arguments[1] !== null) {//pokud je zadan druhy parametr tak lze prejmenovat obal dat
            $matches = preg_split('~/~', $arguments[1]); //rozdeli to na stringy podle /
            $matches = array_reverse($matches); //pole se budou vytvaret zevnitr ven

            $matches[] = 0; //zakladni obal 0=>...

            foreach ($matches as $value) {
                $args = array($value => $args);
            }
        } else {
            $function_name = strtolower(substr($function_name, 0, 1)) . substr($function_name, 1); //nahrazuje lcfirst
            $args = array(array($function_name . "Input" => $args));
        }

        if ($this->profiler) {
            $query = new SkautisQuery($fname, $args, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
        }
        try {
            $ret = parent::__soapCall($fname, $args);

            //pokud obsahuje Output tak vždy vrací pole i s jedním prvkem.
            if (isset($ret->{$fname . "Result"})) {
                if (isset($ret->{$fname . "Result"}->{$fname . "Output"})) {
                    if ($ret->{$fname . "Result"}->{$fname . "Output"} instanceof stdClass) { //vraci pouze jednu hodnotu misto pole?
                        $ret = array($ret->{$fname . "Result"}->{$fname . "Output"}); //vraci pole se stdClass
                    } else {
                        $ret = $ret->{$fname . "Result"}->{$fname . "Output"}; //vraci pole se stdClass
                    }
                } else {
                    $ret = $ret->{$fname . "Result"}; //neobsahuje $fname.Output
                }
            }
            if ($this->profiler) {
                $this->dispatch(self::EVEVENT_SUCCESS, $query->done($ret));
            }
            return $ret; //neobsahuje $fname.Result
        } catch (SoapFault $e) {
            if ($this->profiler) {
                $this->dispatch(self::EVENT_FAILURE, $query->done(NULL, $e));
            }
            if (preg_match('/Uživatel byl odhlášen/', $e->getMessage())) {
                throw new AuthenticationException();
            }
            if (preg_match('/Nemáte oprávnění/', $e->getMessage())) {
                throw new PermissionException($e->getMessage(), $e->getCode(), $e->getPrevious());
            }
            throw new WsdlException($e->getMessage());
        }
    }

}
