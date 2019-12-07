<?php
declare(strict_types = 1);

namespace Skautis\Wsdl\Decorator\Cache;

use Psr\SimpleCache\CacheInterface;
use Skautis\User;
use Skautis\Wsdl\Decorator\AbstractDecorator;
use Skautis\Wsdl\WebServiceInterface;

class CacheDecorator extends AbstractDecorator
{
    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var array<int, string>
     */
    protected static $checkedLoginIds = [];

    /**
     * @var int
     */
    private $ttl;

  /**
   * @param WebServiceInterface $webService
   * @param CacheInterface $cache
   * @param int $ttlSeconds
   */
    public function __construct(
      WebServiceInterface $webService,
      CacheInterface $cache,
      int $ttlSeconds
    ) {
        $this->webService = $webService;
        $this->cache = $cache;
        $this->ttl = $ttlSeconds;
    }

    /**
     * @inheritdoc
     */
    public function call(string $functionName, array $arguments = [])
    {
        $callHash = $this->hashCall($functionName, $arguments);

        // Pozaduj alespon 1 upesny request na server (zadna Exception) - Kontrola prihlaseni
        if (isset($arguments[User::ID_LOGIN]) && !in_array($arguments[User::ID_LOGIN], static::$checkedLoginIds, true)) {
            $response = $this->webService->call($functionName, $arguments);
            $this->cache->set($callHash, $response, $this->ttl);
            static::$checkedLoginIds[] = $arguments[User::ID_LOGIN];

            return $response;
        }

        $cachedResponse = $this->cache->get($callHash, null);
        if ($cachedResponse !== null) {
            return $cachedResponse;
        }

        $response = $this->webService->call($functionName, $arguments);
        $this->cache->set($callHash, $response, $this->ttl);

        return $response;
    }

	/**
	 * @param array<string, mixed> $arguments
	 */
    protected function hashCall(string $functionName, array $arguments): string
    {
        return $functionName . '?' . http_build_query($arguments);
    }
}
