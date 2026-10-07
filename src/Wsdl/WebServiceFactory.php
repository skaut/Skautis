<?php

declare(strict_types=1);

namespace Skaut\Skautis\Wsdl;

use Psr\EventDispatcher\EventDispatcherInterface;
use Skaut\Skautis\InvalidArgumentException;
use SoapClient;

final class WebServiceFactory implements WebServiceFactoryInterface
{
    /** @var class-string<WebServiceInterface> */
    private readonly string $class;

    private ?EventDispatcherInterface $eventDispatcher;

    /**
     * @param string $className class whose constructor accepts SoapClient, the options array and ?EventDispatcherInterface
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $className = WebService::class, ?EventDispatcherInterface $eventDispatcher = null)
    {
        if (! is_a($className, WebServiceInterface::class, true)) {
            throw new InvalidArgumentException("Argument must be class name of a class implementing WebServiceInterface. '$className' given");
        }

        $this->class = $className;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function createWebService(string $url, array $options): WebServiceInterface
    {
        if ($url === '') {
            throw new InvalidArgumentException('WSDL URL cannot be empty.');
        }

        return new ($this->class)(new SoapClient($url, $options), $options, $this->eventDispatcher);
    }

    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        if ($this->eventDispatcher !== null) {
            throw new InvalidArgumentException('Event dispatcher is already set.');
        }

        $this->eventDispatcher = $eventDispatcher;
    }
}
