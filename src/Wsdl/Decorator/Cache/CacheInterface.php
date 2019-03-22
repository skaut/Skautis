<?php
declare(strict_types = 1);

namespace Skautis\Wsdl\Decorator\Cache;

interface CacheInterface
{
    /**
     * Ziska data z cache
     *
     * @return mixed|null Cachovana hodnota nebo null pokud pro klic neni zadna cache
     */
    public function get(string $key);

    /**
     * Ulozi data do cache
     *
     * @var mixed  $value Serializovatelna data
     */
    public function set(string $key, $value): void ;
}
