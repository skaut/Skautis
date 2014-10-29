<?php

namespace SkautIS;

use SkautIS\Exception\AuthenticationException,
    SkautIS\Exception\AbortException,
    SkautIS\Exception\WsdlException,
    SkautIS\Exception\PermissionException,
    SkautIS\Nette\SkautisQuery,
    SoapFault,
    stdClass,
    SoapClient;

/**
 * @author Hána František <sinacek@gmail.com>
 */
class WS extends SoapClient {

    /**
     * základní údaje volané při každém požadavku
     * ID_Application, ID_Login
     * @var array
     */
    private $init;
    public $onEvent = array();
    public $profiler;

    /**
     * @var mixed $wdl Odkaz na WSDL soubor
     * @var array $init Zakladni informace pro vsechny pozadavky
     * @var bool $compression Ma pouzivat kompresi na prenasena data?
     * @var bool $profiler Ma uklada data pro profilovani?
     */
    public function __construct($wsdl, array $init, $compression = TRUE, $profiler = FALSE) {
        $this->init = $init;
        $this->profiler = $profiler;
        if (empty($wsdl)) {
            throw new AbortException("WSDL musí být nastaven");
        }
        $soapOpts['encoding'] = 'utf-8';
        $soapOpts['soap_version'] = SOAP_1_2;
        if ($compression === TRUE) {
            $soapOpts['compression'] = SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP;
        }
        parent::__construct($wsdl, $soapOpts);
    }

    public function __call($function_name, $arguments) {
        if (array_key_exists($function_name, get_class_vars(__CLASS__))) {
            foreach ($this->onEvent as $f) {
                call_user_func_array($f, $arguments);
            }
            return;
        }
        return $this->__soapCall($function_name, $arguments);
    }

    /**
     *
     * @param string $function_name
     * @param array $arguments ([0]=args [1]=cover)
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

        try {
            if($this->profiler){
                $query = new SkautisQuery($fname, $args, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
            }
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
            if($this->profiler){
                $this->onEvent($query->done($ret));
            }
            return $ret; //neobsahuje $fname.Result
        } catch (SoapFault $e) {
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
