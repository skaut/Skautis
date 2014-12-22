<?php

namespace Skautis\Wsdl;

/**
 * @inheritdoc
 */
class WSFactory implements WSFactoryInterface
{

    /** @var string Třída webové služby */
    protected $class;


    /**
     * @param string $class
     */
    public function __construct($class = '\Skautis\Wsdl\WS')
    {
        $this->class = $class;
    }

    /**
     * @inheritdoc
     */
    public function createWS($url, array $options)
    {
        return new $this->class($url, $options);
    }

}
