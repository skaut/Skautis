<?php
declare(strict_types = 1);

namespace Skaut\Skautis\Wsdl;

use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Interface továrny pro vytváření objektů webových služeb
 */
interface WebServiceFactoryInterface
{

    /**
     * Vytvoř nový objekt webové služby
     *
     * @param string $url Adresa WSDL souboru
     * @param array<string, mixed> $options Globální nastavení pro všechny požadavky
     *
     * @return WebServiceInterface
     */
    public function createWebService(string $url, array $options): WebServiceInterface;

    /**
     * Nastaví event dispatcher.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @return void
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void;
}
