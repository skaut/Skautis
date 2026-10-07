<?php

declare(strict_types=1);

namespace Skaut\Skautis\SessionAdapter;

/**
 * In-memory adapter for tests and scripts that do not need persistence.
 */
class FakeAdapter implements AdapterInterface
{
    /** @var array<string, mixed> */
    protected array $data = [];

    public function set(string $name, mixed $object): void
    {
        $this->data[$name] = $object;
    }

    public function has(string $name): bool
    {
        return isset($this->data[$name]);
    }

    public function get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }
}
