<?php
declare(strict_types = 1);

namespace Skaut\Skautis\Wsdl\Decorator;

use Skaut\Skautis\Wsdl\WebServiceInterface;

abstract class AbstractDecorator implements WebServiceInterface
{

    /**
     * @var WebServiceInterface
     */
    protected $webService;


    /**
     * @inheritdoc
     */
    abstract public function call(string $functionName, array $arguments = []);

    /**
     * @inheritdoc
     */
    public function __call(string $functionName, array $arguments)
    {
        return $this->call($functionName, $arguments);
    }

    /**
     * @inheritdoc
     */
    public function subscribe(string $eventName, callable $callback): void
    {
        $this->webService->subscribe($eventName, $callback);
    }
}
