<?php

declare(strict_types=1);

namespace Skaut\Skautis\SessionAdapter;

/**
 * Adapter over the native $_SESSION.
 */
class SessionAdapter implements AdapterInterface
{
    /** @var array<string, mixed> */
    protected array $session;

    public function __construct()
    {
        $sessionId = '__'.self::class;
        if (! isset($_SESSION[$sessionId]) || ! \is_array($_SESSION[$sessionId])) {
            $_SESSION[$sessionId] = [];
        }

        /** @var array<string, mixed> $section */
        $section = &$_SESSION[$sessionId];
        $this->session = &$section;
    }

    public function set(string $name, mixed $object): void
    {
        $this->session[$name] = $object;
    }

    public function has(string $name): bool
    {
        return isset($this->session[$name]);
    }

    public function get(string $name): mixed
    {
        return $this->session[$name] ?? null;
    }
}
