# Cache

Knihovna umožňuje cachováni pomocí [decorator paternu](https://github.com/domnikl/DesignPatternsPHP/tree/master/Structural/Decorator). Pro cachování požadavků na skautis je možno použít [``CacheDecorator``](../../src/Wsdl/Decorator/Cache/CacheDecorator.php).

Pro cachování lze použít jakoukoliv cache implementující interface z [PSR-16](https://www.php-fig.org/psr/psr-16/). Existují bridge pro různé známé cache implementace, například [Symfony](https://symfony.com/doc/current/components/cache/psr6_psr16_adapters.html), [Doctrine](https://github.com/Roave/DoctrineSimpleCache), [Zend](https://docs.zendframework.com/zend-cache/psr16/).

## Příklad
```PHP
// Získame webovou službu ze Skautisu
/** @var Skaut\Skautis\Skautis $skautis */
$webService = $skautis->User;

// Cache do které se má ukládat výsledek API callu
// V tomto případě je použita Symfony cache obalená PSR-16 bridgem
$cache = new \Symfony\Component\Cache\Psr16Cache(new \Symfony\Component\Cache\Adapter\ArrayAdapter());

// Doba po kterou bude výsledek API callu uložen v cache
$timeToLiveInSeconds = 10*60; 


// Vytvoříme cachovanou web service
$cachedWebService = new \Skaut\Skautis\Wsdl\Decorator\Cache\CacheDecorator($webService, $cache, $timeToLiveInSeconds);

// Nyní můžeme použít cachovanou web service stejně jako obyčejnou web service

// Nyní se provede API call na Skautis
$cachedWebService->call('UserDetail', ['ID'=>1940]);

// Další volání se stejnými parametry
// Odpověď je brána z cache a API call se neprovádí
$cachedWebService->call('UserDetail', ['ID'=>1940]);
```

### Pozor
``CacheDecorator`` neumí rozpoznat který API call je read-only a cachuje vše. 
Je tedy třeba věnovat zvýšenou pozornost při použití``CacheDecorator``. 