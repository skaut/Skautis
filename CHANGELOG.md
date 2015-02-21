#Changelog

Cislovani verzi je v souladu se [Sematinckym verzovanim](http://semver.org/)

##Verze 1.x

###v1.2.4
Moznost pouziti vlastni tridy WS pomoci WSFactory

###v1.0
Knihovna vyexportovana z Nette projektu

##Verze 2.x

###DEV
* Zmena namespace SkautIS => Skautis
* Zmena tridy SkautIS => Skautis
* Třídy přímo komunikující se SkautISem vyčlněny do namespace ``Skautis\Wsdl``.
* Třídy a jejich metody přejmenovány na čitelnější verze, např. "WS" -> "WebService"
* Nette komponenty exportovany do vlastniho balicku
* Vyzadovana verze PHP >= 5.4
* Konstruktor udelan public
* Singleton zustava moznosti
* Cas odhlaseni ze Skautisu (``isLoggedIn``, ``getLogoutDate``, ``setLoginData``)
* ``SkautisQuery`` pro profilovani a debugovani
* Dokumentace presunuta do slozky /docs
* [PSR-4 autoloading](http://www.php-fig.org/psr/psr-4/)
* ``SessionAdapter`` pro kompatibilitu s ruznymi frameworky
* Pri zapnutem profilovani Skautis object uchovava log vsech pozadavku pomoci ``SkautisQuery``
* Pridan ``Config`` pro uzivatelske nastaveni
* Pomocne prvky typu singleton - ``getInstance`` presunuty do HelperTrait
* Pro zasilani zprav vytvorena komponenta EventDispatcher (Interface + Trait)
* ``WsdlManager`` pridan pro praci s WS objekty (obstarává veškerou logiku vytváření objektů webových služeb)
* WebService objekty logují SOAP cally do ``SkautisQuery`` vždy, pokud mají zaregistrován listener na událost (náhrada
  za volbu $profiler).
* Abstraktní továrna na objekty webových služeb nahrazena interfacem.
* ``Skautis`` umožňuje jednoduché logování SOAP callů pomocí metod ``enableDebugLog()`` a ``getDebugLog()``.
* Kód obsluhující data přihlášeného uživatele přesunut do nové třídy ``User``.
* Generické výjimky přesunuty do `Skautis` namespace, výjimky webových služeb přesunuty do `Skautis\Wsdl` namespace.
* `BaseException` nahrazena pomocí [marker interface](http://en.wikipedia.org/wiki/Marker_interface_pattern), všechny
  výjimky knihovny je možné odchytit pomocí `Skautis\Exception`.
* `AuthenticationException` a `PermissionException` dědí od obecnější `WsdlException`.
* `WebServiceInterface` pridano. `WebService` uz nededi od `SoapClient`.
* `AbstractDecorator` pridan pro urceni formy dekoratoru.
* `CacheDecorator` pridan pro cachovani pozadavku na Skautis
* `CacheInterface` pridano pro pouziti libovolne cache
* `ArrayCache` pridano pro cache v ramci jednoho pozadavku
