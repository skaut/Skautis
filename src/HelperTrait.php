<?php

declare(strict_types=1);

namespace Skaut\Skautis;

use Skaut\Skautis\SessionAdapter\SessionAdapter;
use Skaut\Skautis\Wsdl\WebServiceFactory;
use Skaut\Skautis\Wsdl\WsdlManager;

trait HelperTrait
{
    /** @var array<string, Skautis> */
    private static array $instances = [];

    /**
     * Shared instance per application id, wired with $_SESSION. Kept for simple scripts;
     * applications with a DI container build the objects themselves.
     */
    public static function getInstance(
        string $appId,
        bool $testMode = Config::TEST_MODE_DISABLED,
        bool $cache = Config::CACHE_DISABLED,
        bool $compression = Config::COMPRESSION_DISABLED,
    ): Skautis {
        $cacheKey = $appId.'-'.($testMode ? 'test' : 'production');

        if (! isset(self::$instances[$cacheKey])) {
            $wsdlManager = new WsdlManager(new WebServiceFactory(), new Config($appId, $testMode, $cache, $compression));
            $user = new User($wsdlManager, new SessionAdapter());

            self::$instances[$cacheKey] = new Skautis($wsdlManager, $user);
        }

        return self::$instances[$cacheKey];
    }
}
