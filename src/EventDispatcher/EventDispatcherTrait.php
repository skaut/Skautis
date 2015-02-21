<?php

namespace Skautis\EventDispatcher;

trait EventDispatcherTrait
{

    /** @var callable[] */
    private $listeners = [];


    /**
     * @param string|null $eventName
     * @return bool
     */
    protected function hasListeners($eventName = null)
    {
        return $eventName === null ? !empty($this->listeners) : !empty($this->listeners[$eventName]);
    }

    /**
     * @param string $eventName
     * @param mixed $data
     */
    protected function dispatch($eventName, $data)
    {
        if (!$this->hasListeners($eventName)) {
            return;
        }

        foreach ($this->listeners[$eventName] as $callback) {
            call_user_func($callback, $data);
        }
    }

    /**
     * @param string $eventName
     * @param callable $callback
     */
    public function subscribe($eventName, callable $callback)
    {
        $this->listeners[$eventName][] = $callback;
    }
}
