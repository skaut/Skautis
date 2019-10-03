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
     * @var array
     */
    protected $init;

    /**
     * @var SoapClient
     */
    protected $soapClient;

    /**
     * @param array $soapOpts Nastaveni SOAP requestu
     * @throws InvalidArgumentException pokud je odkaz na WSDL soubor prázdný
     */
    public function __construct(SoapClient $soapClient, array $soapOpts)
    {
        $this->init = $soapOpts;
        $this->soapClient = $soapClient;
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
     * @param string $functionName Nazev akce k provedeni na WebService
     * @param array $arguments ([0]=args [1]=cover)
     * @param array $options Nastaveni
     * @param array|null $inputHeaders Hlavicky pouzite pri odesilani
     * @param array $outputHeaders Hlavicky ktere prijdou s odpovedi
     *
     * @return mixed
     */
    protected function soapCall(
      string $functionName,
      array $arguments,
      array $options = [],
      ?array $inputHeaders = null,
      array &$outputHeaders = []
    ) {
        $fname = ucfirst($functionName);
        $args = $this->prepareArgs($fname, $arguments);

        if ($this->hasListeners()) {
            $query = new SkautisQuery($fname, $args, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
        }

        try {
            $soapResponse = $this->soapClient->__soapCall($fname, $args, $options, $inputHeaders, $outputHeaders);

            $soapResponse = $this->parseOutput($fname, $soapResponse);

            if (isset($query) && $this->hasListeners()) {
                $this->dispatch(self::EVENT_SUCCESS, $query->done($soapResponse));
            }
            return $soapResponse;
        } catch (SoapFault $e) {
            if (isset($query) && $this->hasListeners()) {
              $this->dispatch(self::EVENT_FAILURE, $query->done(null, $e));
            }

            throw $this->convertToSkautisException($e);
        }
    }

    /**
     * Z defaultnich parametru a parametru callu vytvori argumenty pro SoapClient::__soapCall
     *
     * @param string $functionName Jmeno funkce volane pres SOAP
     * @param array $arguments      Argumenty k mergnuti s defaultnimy
     *
     * @return array Argumenty pro SoapClient::__soapCall
     */
    protected function prepareArgs($functionName, array $arguments): array
    {
        if (!isset($arguments[0]) || !is_array($arguments[0])) {
            $arguments[0] = [];
        }

        //k argumentum připoji vlastni informace o aplikaci a uzivateli
        $args = array_merge($this->init, $arguments[0]);

        if (!isset($arguments[1]) || $arguments[1] === null) {
            $functionName = lcfirst($functionName);
            $args = [[$functionName . 'Input' => $args]];
            return $args;
        }

        //pokud je zadan druhy parametr tak lze prejmenovat obal dat
        $matches = explode('/', $arguments[1]);
        //pole se budou vytvaret zevnitr ven
        $matches = array_reverse($matches);

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
     * @return array
     */
    protected function parseOutput(string $fname, $ret): array
    {
        //pokud obsahuje Output tak vždy vrací pole i s jedním prvkem.
        $result = $ret->{$fname . 'Result'};
        if (!isset($result)) {
            return $ret;
        }

        $output = $result->{$fname . 'Output'};
        if (!isset($output)) {
            return $result; //neobsahuje $fname.Output
        }

        if ($output instanceof stdClass) { //vraci pouze jednu hodnotu misto pole?
            return [$output]; //vraci pole se stdClass
        }

        return $output; //vraci pole se stdClass
    }

    private function convertToSkautisException(SoapFault $e): WsdlException
    {
      if (preg_match('/Uživatel byl odhlášen/ui', $e->getMessage())) {
        return new AuthenticationException($e->getMessage(), $e->getCode(), $e);
      }

      if (preg_match('/Nemáte oprávnění/ui', $e->getMessage())) {
        return new PermissionException($e->getMessage(), $e->getCode(), $e);
      }

      return new WsdlException($e->getMessage(), $e->getCode(), $e);
    }
}
