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
     * @return array<int|string, mixed>|stdClass|null
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
        $trace = null;

        if ($this->eventDispatcher !== null) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $event = new RequestPreEvent($fname, $args, $options, $inputHeaders, $trace);
            $this->eventDispatcher->dispatch($event);
        }

        $requestStart = microtime(true);
        try {
            $soapResponse = $this->soapClient->__soapCall($fname, $args, $options, $inputHeaders, $outputHeaders);
            $soapResponse = $this->parseOutput($fname, $soapResponse);

            if ($this->eventDispatcher !== null) {
                $duration = microtime(true) - $requestStart;
                $event = new RequestPostEvent($fname, $args, $soapResponse, $duration, $trace);
                $this->eventDispatcher->dispatch($event);
            }

            return $soapResponse;
        } catch (Throwable $t) {

            if ($this->eventDispatcher !== null) {
              $duration = microtime(true) - $requestStart;
              $event = new RequestFailEvent($fname, $args, $t, $duration, $trace);
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
     * @return array<int|string, mixed> Argumenty pro SoapClient::__soapCall
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
     * @return array<int|string, mixed>|\stdClass|null
     */
    protected function parseOutput(string $fname, $ret)
    {

        /*
            Pokud se jedna o request ktery ma vracet jednu hodnotu a ta existuje, skautis vraci primo objekt odpovedi
                Napriklad:
                <UnitDetailResponse xmlns="https://is.skaut.cz/">
                    <UnitDetailResult>
                        data jednoho objektu
                    </UnitDetailResult>
                </UnitDetailResponse>

            To SoapClient naparsuje jako:
                class stdClass#25 (1) {
                    public $UnitDetailResult =>
                        class stdClass#26 (48) {
                            data objectu
                        }
                }



            Pokud se jedna o request ktery ma vracet jednu hodnotu a ta neexistuje, skautis vraci jeden self-closing tag
            Napriklad:
                <UnitDetailResponse xmlns="https://is.skaut.cz/" />

            To SoapClient naparsuje jako:
                class stdClass#25 (0) {
                }

            Pokud se jedna o request ktery ma vracet vice hodnot, vraci *Result/*Output
            Napriklad:
                <UnitAllResponse xmlns="https://is.skaut.cz/">
                    <UnitAllResult>
                        <UnitAllOutput>
                            data jednoho objektu
                        </UnitAllOutput>
                        <UnitAllOutput>
                            data dalsiho objektu
                        </UnitAllOutput>
                    </UnitAllResult>
                </UnitAllResponse>

            To SoapClient naparsuje jako:
                class stdClass#25 (1) {
                    public $UnitAllResult =>
                        class stdClass#26 (1) {
                            public $UnitAllOutput =>
                                array(2) {
                                    stdClass - data jednoho objektu,
                                    stdClass - data dalsiho objektu,
                                }
                        }
                    }
                }

            Pokud se jedna o request ktery ma vracet vice hodnot, vraci klasickou dvojici tagu
            Napriklad:
                <UnitAllResponse xmlns="https://is.skaut.cz/">
                    <UnitAllResult />
                </UnitAllResponse>

            To SoapClient naparsuje jako:
                class stdClass#25 (1) {
                    public $UnitAllResult =>
                        class stdClass#26 (0) {
                        }
                }

        */

        if (!$ret) {
            throw new ParsingFailedException('Unexpected output from Skautis');
        }

        // Pokud byl vracen prazdny objekt predstavujici neexistujici vec
        if ($ret instanceof stdClass && count((array) $ret) === 0) {
            return null;
        }

        // Pokud obsahuje *Result pak se  bud jedna o existujici jeden objekt, vice objektu nebo prazdny seznam objektu
        $result = $ret->{$fname . 'Result'} ?? null;
        if (!isset($result)) {
            throw new ParsingFailedException('Unexpected output from Skautis');
        }

        $output = $result->{$fname . 'Output'} ?? null;
        // Pokud obsahuje *Result, ale zadny *Output pak se jedna o jeden
        if (!isset($output)) {
            // Vraci prazdny object
            if ($result instanceof stdClass && count((array) $result) === 0) {
                return [];
            }

            return $result;
        }

        // Vraci pouze jednu hodnotu misto pole?
        if ($output instanceof stdClass) {
            return [$output];
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
