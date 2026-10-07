<?php

declare(strict_types=1);

namespace Skaut\Skautis\Wsdl;

use Psr\EventDispatcher\EventDispatcherInterface;
use Skaut\Skautis\InvalidArgumentException;

interface WebServiceFactoryInterface
{
    /**
     * @param string               $url     WSDL address
     * @param array<string, mixed> $options SoapClient options plus ID_Application and ID_Login
     *
     * @throws InvalidArgumentException
     */
    public function createWebService(string $url, array $options): WebServiceInterface;

    /**
     * Sets the dispatcher for the web services created from now on. Only one dispatcher can be set.
     *
     * @throws InvalidArgumentException when a dispatcher is already set
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void;
}
