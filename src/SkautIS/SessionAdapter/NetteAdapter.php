<?php

namespace SkautIS\SessionAdapter;

use SkautIS\SessionAdapter\AdapterInterface;
use Nette\Http\Session;

/**
 * Adapter pro pouziti Nette Session ve SkautISu
 */
class NetteAdapter implements AdapterInterface
{

    /**
     * @var Nette\Http\SessionSection
     */
    protected $sessionSection;

    public function __construct(Session $session)
    {
        $this->sessionSection = $session->getSection("__" . __CLASS__);
    }

    /**
     * @inheritdoc
     */
    public function set($name, $object)
    {
        $this->sessionSection->$name = $object;
    }

    /**
     * @inheritdoc
     */
    public function has($name)
    {
        return isset($this->sessionSection->$name);
    }


    /**
     * @inheritdoc
     */
    public function get($name)
    {
	return $this->sessionSection->$name;
    }
}
