<?php

namespace Skautis\Factory;

/**
 * Trida umoznujici pouziti vlastni tridy WS se tridou SkautIS
 */
abstract class WSFactory
{

    /**
     * Vytvor novy WS objekt
     *
     * @param string $wsdl     Odkaz na WSDL soubor
     * @param array  $init     Zakladni informace pro vsechny pozadavky
     * @param bool   $profiler Ma ukladat data pro profilovani?
     *
     * @return WS;
     */
    abstract public function createWS($wsdl, array $init, $profiler);
}
