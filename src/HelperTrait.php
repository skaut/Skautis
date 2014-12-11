<?php

namespace Skautis;

use Skautis\Skautis;
use Skautis\Config;
use Skautis\WsdlManager;
use Skautis\Factory\BasicWSFactory;
use Skautis\SessionAdapter\SessionAdapter;

trait HelperTrait
{

    /**
     * sigleton
     * @var Skautis
     */
    private static $instance = NULL;



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
    public static function getInstance($appId = NULL, $testMode = FALSE, $profiler = FALSE, $cache = FALSE)
    {



	if (self::$instance === NULL) {

   	    // Out of box integrace s $_SESSION
            $sessionAdapter = new SessionAdapter();

	    $wsFactory = new BasicWSFactory();
	    $wsdlManager = new WsdlManager($wsFactory, $httpPrefix, $compression, $profiler);

            self::$instance = new self($appId, $sessionAdapter, $wsdlManager, $testMode, $profiler, $cache);
        }


        return self::$instance;
    }
}
