#Cache

Knihovna umoznuje cachovani pomoci [decorator paternu](https://github.com/domnikl/DesignPatternsPHP/tree/master/Structural/Decorator). Pro cachovani pozadavku na skautis je mozno pouzit `CacheDecorator`.


##Priklad
```PHP
//Ziskame webovou sluzbu ze skautisu
$webService = $skautis->User;

//Pouzijeme v knihovne existujici implementaci cache
$cache = new ArrayCache();

//Vytvorime cachovanou web service
$cachedWebService = new CacheDecorator($webService, $cache);

//Nyni muzeme pouzit cachovanou web service jako klasickou web service
$cachedWebService->call('UserDetail', ['ID'=>1940]);
```


##Vlastni implementace cache
Pro pouziti jine implementace cache je potreba vytvorit tridu implementujici [CacheInterface](../src/Wsdl/Decorator/Cache/CacheInterface). Nejjednodusi je pouzit [adapter pattern](https://github.com/domnikl/DesignPatternsPHP/tree/master/Structural/Adapter) v kombinaci s jiz existujici implementaci cache. Napriklad [doctrine cache](https://github.com/doctrine/cache).
