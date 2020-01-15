<?php
declare(strict_types = 1);

namespace Skaut\Skautis\SessionAdapter;

/**
 * Nepersestinenti adapter - vhodne jako stub pro testy nebo kdyz neni potreba ukladat
 */
class FakeAdapter implements AdapterInterface
{
    /**
     * Inmemory storage
     *
     * @var array<string, mixed>
     */
    protected $data = [];

    /**
     * @inheritdoc
     */
    public function set(string $name, $object): void
    {
        $this->data[$name] = $object;
    }

    /**
     * @inheritdoc
     */
    public function has(string $name): bool
    {
        return isset($this->data[$name]);
    }

    /**
     * @inheritdoc
     */
    public function get(string $name)
    {
        return $this->data[$name];
    }
}
