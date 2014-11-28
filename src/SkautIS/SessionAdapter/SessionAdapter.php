<?php

namespace SkautIS\SessionAdapter;

use SkautIS\SessionAdapter\AdapterInterface;

/**
 * Adapter pro pouziti $_SESSION ve SkautISu
 */
class SessionAdapter implements AdapterInterface
{

    /**
     * @var array
     */
    protected $session;

    public function __construct()
    {
	$_SESSION["__" . __CLASS__] = array();
	$this->session = &$_SESSION["__" . __CLASS__];
    }

    /**
     * @inheritdoc
     */
    public function set($name, $object)
    {
        $this->session[$name] = $object;
    }

    /**
     * @inheritdoc
     */
    public function has($name)
    {
        return isset($this->session[$name]);
    }


    /**
     * @inheritdoc
     */
    public function get($name)
    {
	return $this->session[$name];
    }
}
