<?php

namespace Skautis\Wsdl;

use Skautis\EventDispatcher\EventDispatcherInterface;

interface WebServiceInterface extends EventDispatcherInterface
{

    /**
     * Zavola funkci na Skautisu
     *
     * @param string $functionName Jmeno funkce volane na skautisu
     * @param array  $arguments    Argumenty funkce volane na skautisu
     *
     * @return mixed
     */
    public function call($functionName, array $arguments = []);

    /**
     * Zavola funkci na Skautisu
     *
     * @param string $functionName Jmeno funkce volane na skautisu
     * @param array  $arguments    Argumenty funkce volane na skautisu
     *
     * @return mixed
     */
    public function __call($functionName, $arguments);
}
