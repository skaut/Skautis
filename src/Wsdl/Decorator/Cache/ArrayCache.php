<?php

namespace Skautis\Wsdl\Decorator\Cache;

/**
 * Cache v ramci jednoho requestu
 */
class ArrayCache implements CacheInterface
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }
}
