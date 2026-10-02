# Changelog

Číslování verzí je od verze 2.0.0 v souladu se [Sématinckým verzováním](http://semver.org/)

## Verze 3.x

### v3.1.0
* Požadováno PHP 8.4 a novější; CI běží na PHP 8.4 a 8.5 a testy selžou na jakoukoli deprecation.
* Název balíčku je ``skaut/skautis`` (přejmenováno už v 3.0.0-alpha; na Packagistu je zatím jen starší ``skautis/skautis``).
* Typované vlastnosti, parametry a návratové typy v celé knihovně; ``Config`` je ``readonly``.
* Zpětně nekompatibilní: ``WebServiceInterface::call()`` a ``__call()`` deklarují návratový typ ``mixed``; ``SessionAdapter\AdapterInterface::set()`` přijímá ``mixed`` a ``get()`` vrací ``mixed``. Vlastní implementace musí signatury doplnit.
* Zpětně nekompatibilní: události ``RequestPreEvent``, ``RequestPostEvent`` a ``RequestFailEvent`` jsou ``final`` a už neimplementují ``Serializable`` (``serialize()``/``unserialize()`` odstraněny, ``__serialize()``/``__unserialize()`` zůstávají). ``RequestPostEvent`` po deserializaci zachová typ výsledku (``stdClass``, pole nebo ``null``). Délka požadavku se serializuje pod klíčem ``duration`` místo ``time``; události serializované verzí 3.0 jdou stále deserializovat, opačně ne.
* Zpětně nekompatibilní: ``User::updateLogoutTime()`` volá ``LoginUpdateRefresh`` přes ``WebServiceInterface::call()`` místo magické metody (dopad jen na mocky v testech).
* ``Skautis::setLoginData()`` vyhodí ``UnexpectedValueException``, když v datech chybí ``skautIS_Token``.
* ``User::confirmAuth()`` vyhazuje ``Wsdl\AuthenticationException`` místo ``RuntimeException``, když není co potvrdit.
* ``WsdlManager::isMaintenance()`` rozpozná stav 200 i u HTTP/2 odpovědi (dříve jen ``HTTP/1.1 200 OK``).
* Překlad SOAP faultů: ``AuthenticationException`` i pro „Přihlášení vypršelo/neexistuje“ a „není přihlášen“, ``PermissionException`` i pro „nemá oprávnění“, „nedostatečná práva“ a „není povoleno“; výjimka nese původní zprávu a ``getPrevious()``. Výjimky knihovny vyhozené uvnitř požadavku se už nebalí do obecné ``WsdlException``.
* ``WebServiceFactory::setEventDispatcher()`` porovnává striktně.
* ``-read`` anotace třídy ``Skautis`` doplněny o ``DocumentStorage``, ``Grants`` a ``Insurance``.
* ``psr/simple-cache`` ^1.0 || ^2.0 || ^3.0.
* Vývoj: PHPUnit 12, PHPStan 2 (level max bez výjimek), php-cs-fixer 3 (````), GitHub Actions na PHP 8.4 a 8.5, ``Makefile`` a ``docker/Dockerfile`` pro běh bez PHP na hostiteli. Odstraněn pre-commit hook, ``fix_syle.sh`` a ``.scrutinizer.yml``.

### v3.0.0
* Požadována verze PHP >=7.2
* Změna namespace ``\Skautis`` je nyní ``\Skaut\Skautis``
* Místo prázdného objektu typu ``\stdClass`` se  nyní vrací null pro jeden výsledek, nebo prazdne pole pokud se jednalo o dotaz vracející kolekci.
* Odstranění podpory pro HHVM (HHVM dále [nedodržuje kompabilitu s PHP](https://hhvm.com/blog/2018/09/12/end-of-php-support-future-of-hack.html))
* Scalar typehints pro metody
* [Strict types](http://php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration.strict) - zpětně nekompatibilní
* ``EventDispatcher`` používá string místo int pro ``event name`` - zpětně nekompatibilní
* Test mode je nastaven jako defaultní hodnota pro ``Config``, je to tak "bezpečnější" - zpětně nekompatibilní
* ``Config`` je nyní immutable - zpětně nekompatibilní
* Přidána třída s konstantami pro webové služby WebServiceName (rádoby enum)
* PHPDoc annotace pro napovídání názvů webových služeb v IDE a statickou analýzu
* Vlastní cache interface vyměněn za [PSR-16](https://www.php-fig.org/psr/psr-16/). Pro použití s cache různých frameworku existují adaptéry/bridge například [Symfony](https://symfony.com/doc/current/components/cache/psr6_psr16_adapters.html), [Doctrine](https://github.com/Roave/DoctrineSimpleCache), [Zend](https://docs.zendframework.com/zend-cache/psr16/)
* ``isMaintenance`` nyní hází výjimku v případě problému se sítí (například DNS fail) místo PHP warningu
* ``confirmAuth`` a ``updateLogoutTime`` nyní vrací bool jako indikátor uspěchu
* Vlastní event dispatcher byl nahrazen [PSR-14](https://www.php-fig.org/psr/psr-14/) - zpětně nekompatibilní.
* DateTime bylo nahrazeno za DateTimeImmutable

## Verze 2.x

### v2.0.0
* Změna namespace `SkautIS` -> `Skautis`
* Změna třídy `SkautIS` -> `Skautis`
* Třídy přímo komunikující se SkautISem vyčlněny do namespace ``Skautis\Wsdl``.
* Třídy a jejich metody přejmenovány na čitelnější verze, např. `WS` -> `WebService`
* Nette komponenty exportovány do vlastního baličku
* Požadována verze PHP >= 5.4
* Konstruktor udělán public
* Singleton zůstavá možností
* Čas odhlášeni ze Skautisu (``isLoggedIn``, ``getLogoutDate``, ``setLoginData``)
* ``SkautisQuery`` pro profilování a debugováni
* Dokumentace přesunuta do složky [docs](./docs)
* [PSR-4 autoloading](http://www.php-fig.org/psr/psr-4/)
* Přidán ``SessionAdapter`` pro kompatibilitu s ruzn7mi frameworky
* Při zapnutém profilováni Skautis object uchovává log všech požadavků pomoci ``SkautisQuery``
* Přidán ``Config`` pro data aktuální instance
* Pomocné prvky typu singleton - ``getInstance`` přesunuty do HelperTrait
* Pro zasílání zpráv vytvořena komponenta EventDispatcher (Interface + Trait)
* ``WsdlManager`` přidán pro práci s WS objekty (obstarává veškerou logiku vytváření objektů webových služeb)
* WebService objekty logují SOAP cally do ``SkautisQuery`` vždy, pokud mají zaregistrován listener na událost (náhrada
  za volbu $profiler).
* Abstraktní továrna na objekty webových služeb nahrazena interfacem.
* ``Skautis`` umožňuje jednoduché logování SOAP callů pomocí metod ``enableDebugLog()`` a ``getDebugLog()``.
* Kód obsluhující data přihlášeného uživatele přesunut do nové třídy ``User``.
* Generické výjimky přesunuty do `Skautis` namespace, výjimky webových služeb přesunuty do `Skautis\Wsdl` namespace.
* `BaseException` nahrazena pomocí [marker interface](http://en.wikipedia.org/wiki/Marker_interface_pattern), všechny
  výjimky knihovny je možné odchytit pomocí `Skautis\Exception`.
* `AuthenticationException` a `PermissionException` dědí od obecnější `WsdlException`.
* `WebServiceInterface` přidáno. `WebService` již nedědí od `SoapClient`.
* `AbstractDecorator` přidán pro specifikování formy dekorátoru.
* `CacheDecorator` přidán pro cachování požadavků na Skautis
* `CacheInterface` přidáno pro použití libovolné cache
* `ArrayCache` přidáno pro cache v ramci jednoho požadavku


## Verze 1.x

### v1.2.4
Moznost pouziti vlastni tridy `WS` pomoci `WSFactory`

### v1.0
Knihovna vyexportovana z Nette projektu
