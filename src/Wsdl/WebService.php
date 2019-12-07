<?php
declare(strict_types = 1);

namespace Skautis\Wsdl;

use Skautis\EventDispatcher\EventDispatcherTrait;
use Skautis\InvalidArgumentException;
use Skautis\SkautisQuery;
use SoapFault;
use stdClass;
use SoapClient;

/**
 * @author Hána František <sinacek@gmail.com>
 */
class WebService implements WebServiceInterface
{

    use EventDispatcherTrait;

    public const EVENT_SUCCESS = 'success';
    public const EVENT_FAILURE = 'failure';

    /**
     * základní údaje volané při každém požadavku
     * ID_Application, ID_Login
     *
     * @var array<string, mixed>
     */
    protected $init;

    /**
     * @var SoapClient
     */
    protected $soapClient;

    /**
     * @param mixed $wsdl Odkaz na WSDL soubor
     * @param array<string, mixed> $soapOpts Nastaveni SOAP requestu
     * Ma pouzivat kompresi na prenasena data?
     * @throws InvalidArgumentException pokud je odkaz na WSDL soubor prázdný
     */
    public function __construct($wsdl, array $soapOpts)
    {
        $this->init = $soapOpts;
        if (empty($wsdl)) {
            throw new InvalidArgumentException('WSDL address cannot be empty.');
        }

        $this->soapClient = new SoapClient($wsdl, $soapOpts);
    }

    /**
     * @inheritdoc
     */
    public function call(string $functionName, array $arguments = [])
    {
        return $this->soapCall($functionName, $arguments);
    }


    /**
     * @inheritdoc
     */
    public function __call(string $functionName, array $arguments)
    {
        return $this->call($functionName, $arguments);
    }

    /**
     * Metoda provadejici SOAP pozadavek na servery Skautisu
     *
     * @see http://php.net/manual/en/soapclient.soapcall.php
     *
     * @param string $function_name Nazev akce k provedeni na WebService
     * @param array<int|string, mixed> $arguments ([0]=args [1]=cover)
     * @param array<string, mixed> $options Nastaveni
     * @param array<int, string> $input_headers Hlavicky pouzite pri odesilani
     * @param array<int, string> $output_headers Hlavicky ktere prijdou s odpovedi
     * @return mixed
     */
    protected function soapCall(
      string $function_name,
      array $arguments,
      array $options = [],
      array $input_headers = [],
      array &$output_headers = []
    ) {
        $fname = ucfirst($function_name);
        $args = $this->prepareArgs($fname, $arguments);

        if ($this->hasListeners()) {
            $query = new SkautisQuery($fname, $args, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
        }

        try {
            $soapResponse = $this->soapClient->__soapCall($fname, $args, $options, $input_headers, $output_headers);

            $soapResponse = $this->parseOutput($fname, $soapResponse);

            if (isset($query) && $this->hasListeners()) {
                $this->dispatch(self::EVENT_SUCCESS, $query->done($soapResponse));
            }
            return $soapResponse;
        } catch (SoapFault $e) {
            if (isset($query) && $this->hasListeners()) {
                $this->dispatch(self::EVENT_FAILURE, $query->done(null, $e));
            }
            if (preg_match('/Uživatel byl odhlášen/', $e->getMessage())) {
                throw new AuthenticationException($e->getMessage(), $e->getCode(), $e);
            }
            if (preg_match('/Nemáte oprávnění/', $e->getMessage())) {
                throw new PermissionException($e->getMessage(), $e->getCode(), $e);
            }
            throw new WsdlException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Z defaultnich parametru a parametru callu vytvori argumenty pro SoapClient::__soapCall
     *
     * @param string $function_name Jmeno funkce volane pres SOAP
     * @param array<int|string, mixed> $arguments Argumenty k mergnuti s defaultnimy
     *
     * @return array<int, mixed> Argumenty pro SoapClient::__soapCall
     */
    protected function prepareArgs(string $function_name, array $arguments): array
    {
        if (!isset($arguments[0]) || !is_array($arguments[0])) {
            $arguments[0] = [];
        }

        $args = array_merge($this->init, $arguments[0]); //k argumentum připoji vlastni informace o aplikaci a uzivateli

        if (!isset($arguments[1])) {
            $function_name = strtolower(substr($function_name, 0, 1)) . substr($function_name, 1); //nahrazuje lcfirst
            $args = [[$function_name . 'Input' => $args]];
            return $args;
        }

        //pokud je zadan druhy parametr tak lze prejmenovat obal dat
        $matches = explode('/', $arguments[1]); //rozdeli to na stringy podle /
        $matches = array_reverse($matches); //pole se budou vytvaret zevnitr ven

        $matches[] = 0; //zakladni obal 0=>...

        foreach ($matches as $value) {
            $args = [$value => $args];
        }

        return $args;
    }

    /**
     * Parsuje output ze SoapClient do jednotného formátu
     *
     * @param string $fname Jméno funkce volané přes SOAP
     * @param mixed $ret    Odpoveď ze SoapClient::__soapCall
     *
     * @return array<int|string, mixed>
     */
    protected function parseOutput(string $fname, $ret): array
    {
        //pokud obsahuje Output tak vždy vrací pole i s jedním prvkem.
        if (!isset($ret->{$fname . "Result"})) {
            return $ret;
        }

        if (!isset($ret->{$fname . "Result"}->{$fname . "Output"})) {
            return $ret->{$fname . "Result"}; //neobsahuje $fname.Output
        }

        if ($ret->{$fname . "Result"}->{$fname . "Output"} instanceof stdClass) { //vraci pouze jednu hodnotu misto pole?
            return [$ret->{$fname . "Result"}->{$fname . "Output"}]; //vraci pole se stdClass
        }

        return $ret->{$fname . "Result"}->{$fname . "Output"}; //vraci pole se stdClass
    }
}
