# Konfigurace

## Registrace aplikace
Před použitím knihovny je potřeba zaregistrovat aplikaci kterou píšete a získat **Application_ID**. Podrobné informace najdete na  [ws.skauting.cz](http://ws.skauting.cz/).

## Testovaci SkautIS
Protože SkautIS obsahuje důležitá a citlivá data, není vhodné používat ho při vývoji. Pro tento účel je zde [test-is.skaut.cz](http://http://test-is.skaut.cz/).


## Instanciovani knihovny
Centrální část knihovny je třída ``Skautis\Skautis``. Tu je potřeba správně nakonfigurovat.

### Rychlé pomocí singleton patternu
Toto řešení funguje *out of the box* s minimálním nastavením. Je vhodné pro aplikace nevyužívající framework.
```PHP
//ID aplikace ziskane při registraci
$applicationId = "moje-application-id";

//Indikátor jestli se má použít test-is.skaut.cz
$isTestMode = true;

//
$skautis = Skautis\Skautis::getInstance($applicationId, $isTestMode);
```

### Ruční vztvoření
Tento způsob je poněkud zdlouhavý, ale dává možnost maximální flexibility.

#### Konfigurace
Veškerá konfigurace je udržována v jediném objektu, který je potřeba vytvořit a nastavit.
```PHP
//ID aplikace ziskane při registraci
$applicationId = "moje-application-id";

//Indikátor jestli se má použít test-is.skaut.cz
$isTestMode = true;

//Povol cache pro WSDL
$cache = $true;

//Povol kompresi pro data přenášená ze SkautISu
$compression = true;

$config = new Skautis\Config($applicationId, $isTestMode, $cache, $compression);
```

#### Session
Knihovna uchovává nějaké informace mezi requesty. K maximální kompatibilitě mezi různými frameworky knihovna používá [adapter pattern](https://github.com/domnikl/DesignPatternsPHP/tree/master/Structural/Adapter). Adapter pro ``$_SESSION`` je k dispozici v knihovne.
```PHP
//Adapter pro $_SESSION
$sessionAdapter = new Skautis\SessionAdapter\SessionAdapter();
```

#### WebServiceFactory
Tato komponenta se stará o správné vytváření objektů pro webové služby. Jedná se o [abstract factory pattern](https://github.com/domnikl/DesignPatternsPHP/tree/master/Creational/AbstractFactory) který je vhodný když je potřeba pro aplikaci nějakým způsobem upravit vytváření objektů webových služeb. Například přidat logování všech požadavků na SkautIS.
```PHP
$webServiceFactory = new Skautis\Wsdl\WebServiceFactory();
```

#### WsdlManager
Tato třída se stará o vše okolo požadavků na server.
```PHP
$wsdlManager = new Skautis\Wsdl\WsdlManager($webServiceFactory, $config);
```

#### User
Na skautis může být přihlášen právě jeden uživatel. Informace o tomto uživateli jsou mezi requesty uloženy v session.
```PHP
$user = new Skautis\User($wsdlManager, $essionAdapter);
```

#### Skautis
Všechno dohromady lepí třída ``Skautis``. To je také objekt se kterým budete pracovat.
```PHP
$skautis = new Skautis\Skautis($wsdlManager, $user);
```


[Pokračovat na přihlášení](./prihlaseni.md)
