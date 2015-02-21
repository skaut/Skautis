<?php

namespace Skautis;

use Skautis\Wsdl\WsdlManager;
use Skautis\Wsdl\WebServiceFactory;
use Skautis\SessionAdapter\SessionAdapter;

trait HelperTrait
{

    /**
     * sigleton
     * @var Skautis[]
     */
    private static $instances = [];



    /**
     * Ziska sdilenou instanci Skautis objektu
     *
     * Ano vime ze to neni officialni pattern
     * Jedna se o kockopsa Mezi singletonem a StaticFactory
     * Factory metoda na stride kterou instantizuje a novy objekt vytvari jen 1x za beh
     * Proc to tak je? Ohled na zpetnou kompatibilitu a out of the box pouzitelnost pro amatery
     *
     * @var string $appId nastavení appId (nepovinné)
     * @var bool $testMode funguje v testovacím provozu? - výchozí je testovací mode (nepovinné)
     *
     * @return Skautis Sdilena instance Skautis knihovny pro cely beh PHP skriptu
     */
    public static function getInstance($appId, $testMode = false, $cache = false, $compression = false)
    {
        if (!isset(self::$instances[$appId])) {
            $config = new Config($appId);
            $config->setTestMode($testMode);
            $config->setCache($cache);
            $config->setCompression($compression);

            $webServiceFactory = new WebServiceFactory();
            $wsdlManager = new WsdlManager($webServiceFactory, $config);

            // Out of box integrace s $_SESSION
            $sessionAdapter = new SessionAdapter();
            $user = new User($wsdlManager, $sessionAdapter);

            self::$instances[$appId] = new self($wsdlManager, $user);
        }

        return self::$instances[$appId];
    }
}
