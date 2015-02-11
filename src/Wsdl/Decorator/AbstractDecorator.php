<?php

namespace Skautis\Wsdl\Decorator;

use Skautis\Wsdl\WebServiceInterface;
use Skautis\EventDispatcher\EventDispatcherInterface;

abstract class AbstractDecorator implements WebServiceInterface, EventDispatcherInterface
{

    /**
     * @WebServiceInterface
     */
    protected $webService;


    /**
     * @inheritdoc
     */
    abstract public function call($functionName, array $arguments = []);

    /**
     * @inheritdoc
     */
    public function __call($functionName, $arguments) {
        return $this->call($functionName, $arguments);
    }

    /**
     * @inheritdoc
     */
    public function subscribe($eventName, callable $callback)
    {
        $this->webService->subscribe($eventName, $callback);
    }
}
