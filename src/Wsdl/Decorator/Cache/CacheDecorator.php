<?php

declare(strict_types=1);

namespace Skaut\Skautis\Wsdl\Decorator\Cache;

use Psr\SimpleCache\CacheInterface;
use Skaut\Skautis\User;
use Skaut\Skautis\Wsdl\Decorator\AbstractDecorator;
use Skaut\Skautis\Wsdl\WebServiceInterface;

/**
 * Caches responses per method and arguments in any PSR-16 cache.
 */
class CacheDecorator extends AbstractDecorator
{
    /**
     * Login ids that already made one real request through this process; a cached response must not
     * hide an invalid login.
     *
     * @var list<string>
     */
    protected static array $checkedLoginIds = [];

    public function __construct(
        WebServiceInterface $webService,
        protected readonly CacheInterface $cache,
        private readonly int $ttl,
    ) {
        $this->webService = $webService;
    }

    public function call(string $functionName, array $arguments = []): mixed
    {
        $callHash = $this->hashCall($functionName, $arguments);

        $loginId = $arguments[User::ID_LOGIN] ?? null;
        if (\is_string($loginId) && ! \in_array($loginId, static::$checkedLoginIds, true)) {
            $response = $this->webService->call($functionName, $arguments);
            $this->cache->set($callHash, $response, $this->ttl);
            static::$checkedLoginIds[] = $loginId;

            return $response;
        }

        $cachedResponse = $this->cache->get($callHash);
        if ($cachedResponse !== null) {
            return $cachedResponse;
        }

        $response = $this->webService->call($functionName, $arguments);
        $this->cache->set($callHash, $response, $this->ttl);

        return $response;
    }

    /**
     * @param array<int|string, mixed> $arguments
     */
    protected function hashCall(string $functionName, array $arguments): string
    {
        return $functionName.'?'.http_build_query($arguments);
    }
}
