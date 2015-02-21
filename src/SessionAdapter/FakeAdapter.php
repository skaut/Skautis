<?php

namespace Skautis\SessionAdapter;

/**
 * Nepersestinenti adapter - vhodne jako stub pro testy nebo kdyz neni potreba ukladat
 */
class FakeAdapter implements AdapterInterface
{
    /**
     * Inmemory storage
     *
     * @var array
     */
    protected $data = [];

    /**
     * @inheritdoc
     */
    public function set($name, $object)
    {
        $this->data[$name] = $object;
    }

    /**
     * @inheritdoc
     */
    public function has($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * @inheritdoc
     */
    public function get($name)
    {
        return $this->data[$name];
    }
}
