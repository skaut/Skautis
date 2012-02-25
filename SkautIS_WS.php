<?php

/**
 * @author sinacek
 */
class SkautIS_WS extends SoapClient {

    /**
     * základní údaje volané při každém požadavku
     * ID_Application, ID_Login
     * @var array
     */
    private $init;
    /**
     * dependecy on Nette
     * @var bool = FALSE
     */
    private $timer;
    /**
     * dependecy on Nette
     * @var bool = FALSE
     */
    private $arrayHash;

    public function __construct($wsdl, array $init, $compression = TRUE) {
        $this->timer = false;
        $this->arrayHash = false;
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

        //Debugger::barDump($arguments);
        //die();

        $args = array_merge($this->init, $arguments[0]); //k argumentum připoji vlastni informace o aplikaci a uzivateli
        foreach ($args as $key => $value) {//smaže hodnotu kdyz není vyplněna
            if ($value == NULL)
                unset($args[$key]);
        }

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
            if ($this->timer)
                Debugger::timer ("WS-" . $function_name);
            $ret = NULL;
            $ret = parent::__soapCall($fname, $args);
            //dump($ret);die();
            //dump($ret);
//            if ($fname == "PersonAllExport") {
//                //dump($ret);
//            }
            //dump($fname);
            //dump($args);
            //dump($ret);

            if ($this->timer)
                Debugger::timer ("WS-" . $function_name);


            //pokud obsahuje Output tak vždy vrací pole i s jedním prvkem.
            $ret = $this->arrayHash ? $this->toArrayHash($ret) : $ret;
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
            //dump($fname);
            //dump($args);
            //dump($ret);
            //@todo opravit a zkusit vymazat
            $presenter = Environment::getApplication()->getPresenter();
            if (preg_match('/Uživatel byl odhlášen/', $e->getMessage())) {
                Environment::getUser()->logout(TRUE);
                $presenter->flashMessage("Vypršelo přihlášení do skautISu", "fail");
                $presenter->redirect(":Default:");
            }
//            elseif (preg_match('/^Server was unable to process request. ---> Přístup odepřen /', $e->getMessage())) {
//                //po odhlaseni to hazi chybu tak se jenom presmeruje na default
//                return;
//            } elseif (preg_match('/^Server was unable to process request./', $e->getMessage()) ||
//                    preg_match('/^Server was unable to read request/', $e->getMessage())
//            ) {
//                Debugger::log("[SoapSkautIS] " . $e->getMessage());
//                Environment::getUser()->logout(TRUE);
//                $presenter->flashMessage("Provedl jste nepovolenou operaci a byl odhlášen", "fail");
//                $presenter->redirect(":Default:");
//            }

            throw $e;

            //$presenter->redirect(":Auth:");
            //
            //dump($e->__toString());
            //throw new Exception(self::EXCEPT);
        }
    }

    /**
     * prevede stdClass na ArrayHash
     * @param mixed $obj 
     * @deprecated - dependecy on Nette
     */
    function toArrayHash($obj) {
        $obj = ArrayHash::from((array) $obj);
        foreach ($obj as $key => $value) {
            if ($value instanceof stdClass) {
                $obj[$key] = $this->toArrayHash($value);
            }
        }
        return $obj;
    }

}