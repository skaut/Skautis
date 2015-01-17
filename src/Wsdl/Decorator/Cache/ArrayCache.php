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
    public function has($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        return $this->data[$key];
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }
}
