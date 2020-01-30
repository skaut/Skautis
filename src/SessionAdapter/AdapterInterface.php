<?php
declare(strict_types = 1);

namespace Skaut\Skautis\SessionAdapter;

/**
 * Interface umoznujici vytvoreni adapteru pro ruzne implementace Session
 */
interface AdapterInterface
{

    /**
     * Ulozi data do session
     *
     * @param mixed $object
     */
    public function set(string $name, $object): void ;

    /**
     * Overi existenci dat v session
     */
    public function has(string $name): bool;

    /**
     * Ziska data ze session
     *
     * @return mixed
     */
    public function get(string $name);
}
