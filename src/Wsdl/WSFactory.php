<?php

namespace Skautis\Wsdl;

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
     *
     * @return WS;
     */
    abstract public function createWS($wsdl, array $init);
}
