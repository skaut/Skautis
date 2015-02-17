<?php

namespace Skautis\Wsdl;

/**
 * @inheritdoc
 */
class WebServiceFactory implements WebServiceFactoryInterface
{

    /** @var string Třída webové služby */
    protected $class;


    /**
     * @param string $class
     */
    public function __construct($class = '\Skautis\Wsdl\WebService')
    {
        $this->class = $class;
    }

    /**
     * @inheritdoc
     */
    public function createWebService($url, array $options)
    {
        return new $this->class($url, $options);
    }
}
