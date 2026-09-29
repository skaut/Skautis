<?php

declare(strict_types=1);

namespace Skaut\Skautis\SessionAdapter;

/**
 * Storage for login data; implement it over the session of your framework.
 */
interface AdapterInterface
{
    public function set(string $name, mixed $object): void;

    public function has(string $name): bool;

    public function get(string $name): mixed;
}
