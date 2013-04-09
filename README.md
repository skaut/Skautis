SkautIS
=======

knihovna pro připojení do SkautISu

# Jak připojit knihovnu?
## Základní nastavení
Před prvním dotazem na SkautIS musíme nastavit **Application_ID** jedinečné pro naši aplikaci. Jako druhý argument můžeme uvést jestli aplikace běží v **testovacím režimu**. Výchozí nastavení je TRUE.

```php
<?php
$skautIS = SkautIS::getInstance("moje-application-id", $isTestMode = TRUE);
```

nebo

```php
<?php
$skautIS = SkautIS::getInstance();
$skautIS->setAppId("moje-application-id");
$skautIS->setTestMode(FALSE);
```

nebo

```php
<?php
define("SkautIS_ID_Application", "moje-application-id");
$skautIS = SkautIS::getInstance();
```

## Přihlášení
```php
<?php
echo '<a href="'.$skautIS->getLoginUrl();.'"> Prihlasit se</a>'; //Vypise odkaz pro prihlaseni do SkautISu
```

Po úspěšném přihlášení jsme přesměrováni na předem nastavenou adresu (viz. [nápověda](http://is.skaut.cz/napoveda/programatori.3-naprogramovani-obslouzeni-uspesneho-prihlaseni-a-odhlaseni.ashx#Hodnoty_zaslan%C3%A9_webov%C3%A9_str%C3%A1nce_po_%C3%BAsp%C4%9B%C5%A1n%C3%A9m_p%C5%99ihl%C3%A1%C5%A1en%C3%AD_u%C5%BEivatele_0)) a pošle nám údaje přes $_POST
(ID_Login, ID_Role, ID_Unit). Ty nastavíme knihovně, která si je zapamatuje.
```php
<?php
$skautIS->setInit(array(
                "token" => $_POST['skautIS_Token'],
                "roleId" => $_POST['skautIS_IDRole'],
                "unitId" => $_POST['skautIS_IDUnit']
            ));
```
## Získání dat ze SkautISu
```php
<?php
$data = $skautIS->nazev_webove_sluzby->nazev_funkce(array("nazev_atributu"=>"hodnota_atributu", ...));
```
Pro přístup k webové službě SkautISu můžeme použít její plný název nebo alias.

Již předdefinované aliasy:

* usr => user => UserManagement
* org => OrganizationUnit
* app => ApplicationManagement
* event => events => Events

Argumenty zadáváme v asociativním poli "nazev_atributu"=>"hodnota_atributu". Pokud je atribut ID_Login nastaven, je automaticky přidán ke všem požadavkům.

```php
<?php
$data = $skautIS->user->UserDetail(array("ID"=>1940)); //1940 je ID uzivatele okres blansko
```
## Návratové hodnoty
Data se vrací jako object třídy [stdClass](http://php.net/manual/en/reserved.classes.php) nebo jako pole, pokud třída vrací $nazevTridy.Output. (např. [RoleAllOutput](https://is.skaut.cz/JunakWebservice/UserManagement.asmx?op=RoleAll))


