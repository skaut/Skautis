<?php

declare(strict_types=1);

namespace Skaut\Skautis\Wsdl;

use Psr\EventDispatcher\EventDispatcherInterface;
use Skaut\Skautis\Wsdl\Event\RequestFailEvent;
use Skaut\Skautis\Wsdl\Event\RequestPostEvent;
use Skaut\Skautis\Wsdl\Event\RequestPreEvent;
use SoapClient;
use stdClass;
use Throwable;

/**
 * One skautIS web service (UserManagement, OrganizationUnit, ...).
 *
 * @author Hána František <sinacek@gmail.com>
 */
class WebService implements WebServiceInterface
{
    /**
     * @param array<string, mixed> $init values sent with every request (ID_Application, ID_Login)
     */
    public function __construct(
        protected readonly SoapClient $soapClient,
        protected readonly array $init,
        private readonly ?EventDispatcherInterface $eventDispatcher = null,
    ) {
    }

    public function call(string $functionName, array $arguments = []): mixed
    {
        return $this->soapCall($functionName, $arguments);
    }

    public function __call(string $functionName, array $arguments): mixed
    {
        return $this->call($functionName, $arguments);
    }

    /**
     * @see https://www.php.net/manual/en/soapclient.soapcall.php
     *
     * @param array<int|string, mixed> $arguments     [0] => arguments, [1] => custom input wrapper
     * @param array<string, mixed>     $options
     * @param array<int, mixed>        $inputHeaders
     * @param array<int|string, mixed> $outputHeaders
     *
     * @return array<int|string, mixed>|stdClass|bool|int|float|string|null
     *
     * @throws WsdlException
     */
    protected function soapCall(
        string $functionName,
        array $arguments,
        array $options = [],
        array $inputHeaders = [],
        array &$outputHeaders = [],
    ): array|stdClass|bool|int|float|string|null {
        $fname = ucfirst($functionName);
        $args = $this->prepareArgs($fname, $arguments);
        $trace = [];

        if ($this->eventDispatcher !== null) {
            $trace = debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS);
            $this->eventDispatcher->dispatch(new RequestPreEvent($fname, $args, $options, $inputHeaders, $trace));
        }

        $requestStart = microtime(true);
        try {
            $headers = [];
            $soapResponse = $this->soapClient->__soapCall($fname, $args, $options, $inputHeaders, $headers);
            $outputHeaders = \is_array($headers) ? $headers : [];
            $response = $this->parseOutput($fname, $soapResponse);

            $this->eventDispatcher?->dispatch(new RequestPostEvent($fname, $args, $response, microtime(true) - $requestStart, $trace));

            return $response;
        } catch (Throwable $throwable) {
            $this->eventDispatcher?->dispatch(new RequestFailEvent($fname, $args, $throwable, microtime(true) - $requestStart, $trace));

            throw $this->convertToSkautisException($throwable);
        }
    }

    /**
     * Merges the per-request defaults into the arguments and wraps them the way skautIS expects:
     * [['unitDetailInput' => [...]]], or a custom wrapper given as the second argument ('a/b' nests b into a).
     *
     * @param array<int|string, mixed> $arguments
     *
     * @return array<int|string, mixed>
     */
    protected function prepareArgs(string $functionName, array $arguments): array
    {
        $callArguments = isset($arguments[0]) && \is_array($arguments[0]) ? $arguments[0] : [];
        $args = array_merge($this->init, $callArguments);

        if (! isset($arguments[1]) || ! \is_string($arguments[1])) {
            return [[lcfirst($functionName).'Input' => $args]];
        }

        $wrappers = array_reverse(explode('/', $arguments[1]));
        $wrappers[] = 0;

        foreach ($wrappers as $wrapper) {
            $args = [$wrapper => $args];
        }

        return $args;
    }

    /**
     * Normalises the SoapClient response.
     *
     * skautIS wraps everything in <{Method}Result>. A single existing record comes as an object,
     * a missing record as an empty self-closing element, a collection as <{Method}Output> elements
     * (one element is a single object, not an array) and an empty collection as an empty <{Method}Result>.
     *
     * @return array<int|string, mixed>|stdClass|bool|int|float|string|null null for a missing record or a nil result, [] for an empty collection, a scalar as is
     *
     * @throws ParsingFailedException
     */
    protected function parseOutput(string $fname, mixed $ret): array|stdClass|bool|int|float|string|null
    {
        if (! $ret instanceof stdClass) {
            throw new ParsingFailedException(\sprintf('Unexpected response to %s: %s', $fname, get_debug_type($ret)));
        }

        // empty self-closing element: the record does not exist
        if ((array) $ret === []) {
            return null;
        }

        if (! property_exists($ret, $fname.'Result')) {
            throw new ParsingFailedException(\sprintf('Response to %s has no %sResult', $fname, $fname));
        }

        // <{Method}Result xsi:nil="true"/>: no value
        $result = $ret->{$fname.'Result'};
        if ($result === null) {
            return null;
        }

        // array or a scalar value (bool, number, string) is returned as is
        if (\is_array($result) || \is_scalar($result)) {
            return $result;
        }

        if (! $result instanceof stdClass) {
            throw new ParsingFailedException(\sprintf('Unexpected %sResult type: %s', $fname, get_debug_type($result)));
        }

        $output = $result->{$fname.'Output'} ?? null;
        if ($output === null) {
            // empty <{Method}Result/> is an empty collection, anything else is one record
            return (array) $result === [] ? [] : $result;
        }

        if ($output instanceof stdClass) {
            return [$output];
        }

        return \is_array($output) ? $output : [$output];
    }

    /**
     * skautIS reports business errors as SOAP faults with a Czech message.
     */
    private function convertToSkautisException(Throwable $throwable): WsdlException
    {
        if ($throwable instanceof WsdlException) {
            return $throwable;
        }

        $message = $throwable->getMessage();

        if (preg_match('~(byl odhlášen|přihlášení (vypršelo|neexistuje)|není přihlášen)~ui', $message) === 1) {
            return new AuthenticationException($message, (int) $throwable->getCode(), $throwable);
        }

        if (preg_match('~(nem(?:áte|á) oprávnění|nedostatečná práva)~ui', $message) === 1) {
            return new PermissionException($message, (int) $throwable->getCode(), $throwable);
        }

        return new WsdlException($message, (int) $throwable->getCode(), $throwable);
    }
}
