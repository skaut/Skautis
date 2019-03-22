<?php
declare(strict_types = 1);

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
    public function get(string $key)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }
}
