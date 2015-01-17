<?php

namespace Skautis\Wsdl\Decorator\Cache;

interface CacheInterface
{
    /**
     * Ziska data z cache
     *
     * @var string $key
     *
     * @return mixed|null Cachovana hodnota nebo null pokud pro klic neni zadna cache
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
