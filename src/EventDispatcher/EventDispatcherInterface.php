<?php

namespace Skautis\EventDispatcher;

interface EventDispatcherInterface
{

    /**
     * Přidá listener na událost.
     *
     * @param string $eventName
     * @param callable $callback
     */
    public function subscribe($eventName, callable $callback);
}
