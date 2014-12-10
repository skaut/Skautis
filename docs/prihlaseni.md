#Prihlaseni
```php
<?php
echo '<a href="'.$skautis->getLoginUrl();.'"> Prihlasit se</a>'; //Vypise odkaz pro prihlaseni do skautisu
```

Po úspěšném přihlášení jsme přesměrováni na předem nastavenou adresu (viz. [nápověda](http://is.skaut.cz/napoveda/programatori.3-naprogramovani-obslouzeni-uspesneho-prihlaseni-a-odhlaseni.ashx#Hodnoty_zaslan%C3%A9_webov%C3%A9_str%C3%A1nce_po_%C3%BAsp%C4%9B%C5%A1n%C3%A9m_p%C5%99ihl%C3%A1%C5%A1en%C3%AD_u%C5%BEivatele_0)) a pošle nám údaje přes $_POST
(ID_Login, ID_Role, ID_Unit). Ty nastavíme knihovně, která si je zapamatuje.


```php
<?php
$skautis->setLoginData($_POST);
```

nebo

```php
<?php
$skautis->setToken($_POST['skautIS_Token']);
$skautis->setRoleId($_POST['skautIS_IDRole']);
$skautis->setUnitId($_POST['skautIS_IDUnit']);
```

[Pokracovat na pouziti](./pouziti.md)

