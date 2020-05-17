<?php
declare(strict_types = 1);

namespace Skaut\Skautis\Wsdl;

use Psr\EventDispatcher\EventDispatcherInterface;
use Skaut\Skautis\InvalidArgumentException;
use Skaut\Skautis\Wsdl\Event\RequestFailEvent;
use Skaut\Skautis\Wsdl\Event\RequestPostEvent;
use Skaut\Skautis\Wsdl\Event\RequestPreEvent;
use SoapClient;
use stdClass;
use Throwable;

/**
 * @author Hána František <sinacek@gmail.com>
 */
class WebService implements WebServiceInterface
{

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
     * @var EventDispatcherInterface|null
     */
    private $eventDispatcher;

    /**
     * @param array<string, mixed> $soapOpts Nastaveni SOAP requestu
     * @throws InvalidArgumentException pokud je odkaz na WSDL soubor prázdný
     */
    public function __construct(
      SoapClient $soapClient,
      array $soapOpts,
      ?EventDispatcherInterface $eventDispatcher
    ) {
        $this->init = $soapOpts;
        $this->soapClient = $soapClient;
        $this->eventDispatcher = $eventDispatcher;
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
     * @param array<int|string, mixed> $arguments ([0]=args [1]=cover)
     * @param array<string, mixed> $options Nastaveni
     * @param array<int, string> $inputHeaders Hlavicky pouzite pri odesilani
     * @param array<int, string> $outputHeaders Hlavicky ktere prijdou s odpovedi
     * @return mixed
     */
    protected function soapCall(
      string $functionName,
      array $arguments,
      array $options = [],
      array $inputHeaders = [],
      array &$outputHeaders = []
    ) {
        $fname = ucfirst($functionName);
        $args = $this->prepareArgs($fname, $arguments);

        if ($this->eventDispatcher !== null) {
            $event = new RequestPreEvent($fname, $args, $options, $inputHeaders, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
            $this->eventDispatcher->dispatch($event);
        }

        $requestStart = microtime(true);
        try {
            $soapResponse = $this->soapClient->__soapCall($fname, $args, $options, $inputHeaders, $outputHeaders);
            $soapResponse = $this->parseOutput($fname, $soapResponse);

            if ($this->eventDispatcher !== null) {
                $duration = microtime(true) - $requestStart;
                $event = new RequestPostEvent($fname, $args, $soapResponse, $duration);
                $this->eventDispatcher->dispatch($event);
            }

            return $soapResponse;
        } catch (Throwable $t) {

            if ($this->eventDispatcher !== null) {
              $duration = microtime(true) - $requestStart;
              $event = new RequestFailEvent($fname, $args, $t, $duration);
              $this->eventDispatcher->dispatch($event);
            }

            throw $this->convertToSkautisException($t);
        }
    }

    /**
     * Z defaultnich parametru a parametru callu vytvori argumenty pro SoapClient::__soapCall
     *
     * @param string $functionName Jmeno funkce volane pres SOAP
     * @param array<int|string, mixed> $arguments Argumenty k mergnuti s defaultnimy
     *
     * @return array<int, mixed> Argumenty pro SoapClient::__soapCall
     */
    protected function prepareArgs(string $functionName, array $arguments): array
    {
        if (!isset($arguments[0]) || !is_array($arguments[0])) {
            $arguments[0] = [];
        }

        //k argumentum připoji vlastni informace o aplikaci a uzivateli
        $args = array_merge($this->init, $arguments[0]);

        if (!isset($arguments[1])) {
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
     * @return array<int|string, mixed>
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

    private function convertToSkautisException(Throwable $e): WsdlException
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
