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
* ``WsdlManager`` pridan pro praci s WS objekty
