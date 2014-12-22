<?php

namespace Skautis;

use Skautis\Wsdl\WsdlManager;
use Skautis\Wsdl\WSFactory;
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
     * @var bool $profiler ma uchovavat data pro profilovani?
     *
     * @return Skautis Sdilena instance Skautis knihovny pro cely beh PHP skriptu
     * @throws InvalidArgumentException
     */
    public static function getInstance($appId, $testMode = false, $profiler = false, $cache = false, $compression = false)
    {



        if ( !key_exists($appId, self::$instances) )
        {

            $config = new Config($appId);
	    $config->setTestMode($testMode);
	    $config->setCache($cache);
	    $config->setCompression($compression);
	    $config->setProfiler($profiler);



   	    // Out of box integrace s $_SESSION
            $sessionAdapter = new SessionAdapter();

	    $wsFactory = new WSFactory();
	    $wsdlManager = new WsdlManager($wsFactory, $config);

            self::$instances[$appId] = new self($config, $wsdlManager, $sessionAdapter);
        }


        return self::$instances[$appId];
    }
}
