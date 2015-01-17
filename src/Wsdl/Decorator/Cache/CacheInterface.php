<?php

namespace Skautis\Wsdl\Decorator\Cache;

interface CacheInterface
{
    /**
     * Zjisti jestli je dany klic v cache
     *
     * @var string $key
     *
     * @return bool
     */
    public function has($key);

    /**
     * Ziska data z cache
     *
     * @var string $key
     *
     * @return mixed
     */
    public function get($key);

    /**
     * Ulozi data do cache
     *
     * @var string $key
     * @var mixed  $value Serializovatelna data
     *
     * @return void
     */
    public function set($key, $value);
}
