#Changelog

Číslování verzí je od verze 2.0.0 v souladu se [Sématinckým verzováním](http://semver.org/)

##Verze 2.x

###v2.0.0
* Změna namespace SkautIS => Skautis
* Změna třídy SkautIS => Skautis
* Třídy přímo komunikující se SkautISem vyčlněny do namespace ``Skautis\Wsdl``.
* Třídy a jejich metody přejmenovány na čitelnější verze, např. "WS" -> "WebService"
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


##Verze 1.x

###v1.2.4
Moznost pouziti vlastni tridy WS pomoci WSFactory

###v1.0
Knihovna vyexportovana z Nette projektu
