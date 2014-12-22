<?php

namespace Skautis\Wsdl;


/**
 * @inheritdoc
 */
class BasicWSFactory extends WSFactory
{

   /**
     * Trida WS
     * @var string
     */
    protected $class;

    public function __construct($class = null)
    {
        if ($class === NULL) {
            $this->class = '\Skautis\Wsdl\WS';
	    return;
	}

	$this->class = $class;
    }

    /**
     * @inheritdoc
     */
    public function createWS($wsdl, array $init)
    {
        return new $this->class($wsdl, $init);
    }

}
