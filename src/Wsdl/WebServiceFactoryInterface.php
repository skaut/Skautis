<?php

namespace Skautis\Wsdl;

/**
 * Interface továrny pro vytváření objektů webových služeb
 */
interface WebServiceFactoryInterface
{

    /**
     * Vytvoř nový objekt webové služby
     *
     * @param string $url Adresa WSDL souboru
     * @param array $options Globální nastavení pro všechny požadavky
     * @return mixed
     */
    public function createWebService($url, array $options);
}
