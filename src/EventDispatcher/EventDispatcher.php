<?php

namespace Skautis\EventDispatcher;

interface EventDispatcher
{
    /**
     * Nastavi listener na udalost
     *
     * @return void
     */
    public function subscribe($eventName, callable $callback);
}
