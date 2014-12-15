<?php

namespace Skautis\EventDispatcher;

interface EventDispatcherInterface
{
    /**
     * Nastavi listener na udalost
     *
     * @return void
     */
    public function subscribe($eventName, callable $callback);
}
