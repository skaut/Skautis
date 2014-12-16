<?php

namespace Skautis\EventDispatcher;

trait EventDispatcherTrait
{


    /**
     * @vat callable[]
     */
    protected $listeners = [];

    protected function dispatch($eventName, $data)
    {
        if (!key_exists($eventName, $this->listeners)) {
            return;
        }

        foreach ($this->listeners[$eventName] as $callback) {
            call_user_func($callback, $data);
        }
    }

    public function subscribe($eventName, callable $callback)
    {
        if (!key_exists($eventName, $this->listeners)) {
            $this->listeners[$eventName] = [];
	}

        $this->listeners[$eventName][] = $callback;
    }
}
