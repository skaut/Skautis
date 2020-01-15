<?php
declare(strict_types = 1);

namespace Skaut\Skautis\EventDispatcher;

trait EventDispatcherTrait
{

    /** @var array<string, callable[]> */
    private $listeners = [];


    protected function hasListeners(?string $eventName = null): bool
    {
        return $eventName === null ? !empty($this->listeners) : !empty($this->listeners[$eventName]);
    }

    /**
     * @param mixed $data
     */
    protected function dispatch(string $eventName, $data): void
    {
        if (!$this->hasListeners($eventName)) {
            return;
        }

        foreach ($this->listeners[$eventName] as $callback) {
            $callback($data);
        }
    }

    public function subscribe(string $eventName, callable $callback): void
    {
        $this->listeners[$eventName][] = $callback;
    }
}
