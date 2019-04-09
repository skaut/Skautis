<?php
declare(strict_types = 1);

namespace Skautis\EventDispatcher;

interface EventDispatcherInterface
{

    /**
     * Přidá listener na událost.
     */
    public function subscribe(string $eventName, callable $callback): void;
}
