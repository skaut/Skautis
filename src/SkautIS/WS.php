<?php

namespace SkautIS;

use SkautIS\Exception\AuthenticationException;
use SoapFault;
use stdClass;
use SoapClient;
use Exception;

/**
 * @author sinacek
 */
class WS extends SoapClient {

    /**
     * základní údaje volané při každém požadavku
     * ID_Application, ID_Login
     * @var array
     */
    private $init;

    public function __construct($wsdl, array $init, $compression = TRUE) {
        $this->init = $init;
        if (!isset($wsdl))
            throw new Exception("WSDL musí být nastaven");
        $soapOpts['encoding'] = 'utf-8';
        $soapOpts['soap_version'] = SOAP_1_2;
        if ($compression === TRUE)
            $soapOpts['compression'] = SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP;
        parent::__construct($wsdl, $soapOpts);
    }

    public function __call($function_name, $arguments) {
        //dump($arguments);
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
//        foreach ($args as $key => $value) {//smaže hodnotu kdyz není vyplněna
//            if ($value == NULL)
//                unset($args[$key]);
//        }
//Debugger::log(Debugger::dump($args,true), "My");
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
            $ret = parent::__soapCall($fname, $args);
//            if(Strings::startsWith($fname, "EventCamp"))
//                Debugger::log(Debugger::dump($args, TRUE), "to");

            //pokud obsahuje Output tak vždy vrací pole i s jedním prvkem.
            if (isset($ret->{$fname . "Result"})) {
                if (isset($ret->{$fname . "Result"}->{$fname . "Output"})) {
                    if($ret->{$fname . "Result"}->{$fname . "Output"} instanceof stdClass){ //vraci pouze jednu hodnotu misto pole?
                        return array($ret->{$fname . "Result"}->{$fname . "Output"}); //vraci pole se stdClass
                    }
                    return $ret->{$fname . "Result"}->{$fname . "Output"}; //vraci pole se stdClass
                }
                return $ret->{$fname . "Result"}; //neobsahuje $fname.Output
            }
            return $ret; //neobsahuje $fname.Result
        } catch (SoapFault $e) {
            //$presenter = Environment::getApplication()->getPresenter();
            if (preg_match('/Uživatel byl odhlášen/', $e->getMessage())) {
                throw new AuthenticationException();
            }
            //dump($e);
            throw new Exception($e->getMessage());
        }
    }

}
