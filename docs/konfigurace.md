#Konfigurace
##Základní nastavení
Před prvním dotazem na SkautIS musíme nastavit **Application_ID** jedinečné pro naši aplikaci. Jako druhý argument můžeme uvést jestli aplikace běží v **testovacím režimu**. Výchozí nastavení je TRUE.

```php
$skautis = SkautIS::getInstance("moje-application-id", $isTestMode = TRUE);
```

[Pokracovat na prihlaseni](./prihlaseni.md)

##Pokrocile nastaveni
```PHP
use Skautis\Skautis;
$skautis = new Skautis('moje-application_id');
```

###WebServiceFactoryInterface
Slouzi k vytvareni WebService objektu
@TODO

###SessionAdapter
Slouzi k ulozeni informaci mezi http pozadavky
@TODO


[Pokracovat na prihlaseni](./prihlaseni.md)
