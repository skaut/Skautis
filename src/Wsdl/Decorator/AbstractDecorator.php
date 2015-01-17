<?php

namespace Skautis\Wsdl\Decorator;

use Skautis\Wsdl\WebServiceInterface;

abstract class AbstractDecorator implements WebServiceInterface
{

    /**
     * @WebServiceInterface
     */
    protected $webService;


    /**
     * @inheritdoc
     */
    abstract function call($functionName, array $arguments = []);
}
