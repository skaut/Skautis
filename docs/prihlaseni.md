#Prihlaseni
Celý proces přihlášení do externí aplikace je popsán v [dokumentaci SkautISu](http://is.skaut.cz/napoveda/programatori.3-naprogramovani-obslouzeni-uspesneho-prihlaseni-a-odhlaseni.ashx#Hodnoty_zaslan%C3%A9_webov%C3%A9_str%C3%A1nce_po_%C3%BAsp%C4%9B%C5%A1n%C3%A9m_p%C5%99ihl%C3%A1%C5%A1en%C3%AD_u%C5%BEivatele_0). Knihovna tento proces velmi zjednodušuje.i

##Generování odkazu pro přihlášení
Knihovna umožňuje vygenerovat odkaz, který přivede uživatele na stránky skautisu a po úspěšném příhlášení ho SkautIS přesměruje zpět do aplikace na předem definovanou adresu.
```php
<?php
//Odkaz na který je uživatel přesměrován po úspěšném přihlášení
$backLink = "https://moje-skautska-aplikace.skaut.cz/skautis-login-confirm";

//Vygenerování odkazu
$loginUrl = $skautis->getLoginUrl($backLink);

//Odkaz je klasická URL která se použít v jakémkoliv templatovacím jazyce nebo rovnou vypsat
echo '<a href="' . $loginUrl . '"> Prihlasit se</a>';
```

##Potvrzení přihlášení
SkautIS uživatele po úspěšném přihlášení přesměruje na adresu nastavenou v předchozím kroce a pošle nám údaje přes $_POST. Tyto údaje je potřeba předat knihovně aby mohla komunikovat se skautisem.
```php
<?php
//Na url https://moje-skautska-aplikace.skaut.cz/skautis-login-confirm
$skautis->setLoginData($_POST);
```


[Pokračovat na použití](./pouziti.md)

