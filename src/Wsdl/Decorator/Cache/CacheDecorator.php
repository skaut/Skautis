<?php

namespace Skautis\Wsdl\Decorator\Cache;

use Skautis\Wsdl\Decorator\AbstractDecorator;
use Skautis\Wsdl\WebServiceInterface;

class CacheDecorator extends AbstractDecorator
{
    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var bool
     */
    protected $succesfullRequest;

    public function __construct(WebServiceInterface $webService, CacheInterface $cache)
    {
        $this->webService = $webService;
        $this->cache = $cache;
    }

    /**
     * @inheritdoc
     */
    public function call($functionName, array $arguments = [])
    {
        $callHash = $this->hashCall($functionName, $arguments);

        // Pozaduj alespon 1 supesny request na server (zadna Exception)
        if (!$this->succesfullRequest) {
            $response = $this->webService->call($functionName, $arguments);
            $this->cache->set($callHash, $response);
            $this->succesfullRequest = true;

            return $response;
        }

        $cachedResponse = $this->cache->get($callHash);
        if ($cachedResponse !== null) {
            return $cachedResponse;
        }

        $response = $this->webService->call($functionName, $arguments);
        $this->cache->set($callHash, $response);

        return $response;
    }

    /**
     * @var string $functionName
     * @var array  $arguments
     */
    protected function hashCall($functionName, array $arguments)
    {
        return $functionName . '?' . http_build_query($arguments);
    }
}
