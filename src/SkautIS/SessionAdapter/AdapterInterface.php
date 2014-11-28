<?php

namespace SkautIS\SessionAdapter;

use SkautIS\SessionAdapter\AdapterInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Interface umoznujici vytvoreni adapteru pro ruzne implementace Session
 */
interface AdapterInterface
{


    /**
     * Ulozi data do session
     *
     * @return void
     */
    public function set($name, $object);

    /**
     * Overi existenci dat v session
     *
     * @return bool
     */
    public function has($name);


    /**
     * Ziska data ze session
     *
     * @return mixed
     */
    public function get($name);
}
