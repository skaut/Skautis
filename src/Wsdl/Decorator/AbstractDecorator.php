<?php

declare(strict_types=1);

namespace Skaut\Skautis\Wsdl\Decorator;

use Skaut\Skautis\Wsdl\WebServiceInterface;

abstract class AbstractDecorator implements WebServiceInterface
{
    protected WebServiceInterface $webService;

    abstract public function call(string $functionName, array $arguments = []): mixed;

    public function __call(string $functionName, array $arguments): mixed
    {
        return $this->call($functionName, $arguments);
    }
}
