SkautIS
=======

PHP knihovna pro připojení do Skautisu

# Jak připojit knihovnu?
## Instalace
Před používáním knihovny je potřeba knihovnu nainstalovat do webové aplikace. K dispozici jsou dva způsoby

### Klasický způsob
Stáhnout zdrojové kódy a pomocí ``include``/``require`` vložit do webové aplikace.

### S použitím balíčkovacího systému composer
Composer je balíčkovací systém usnadňující práci s knihovnami, Detailnější informace najdete na [http://getcomposer.org/doc](http://getcomposer.org/doc/)

* stáhněte composer z [http://getcomposer.com](http://getcomposer.com)
* pomocí konzole spusťte příkaz ``composer require skautis/skautis:2.0.*``
* pomocí konzole nainstalujte závislosti ``composer install``

## Základní nastavení
Před prvním dotazem na SkautIS musíme nastavit **Application_ID** jedinečné pro naši aplikaci. Jako druhý argument můžeme uvést jestli aplikace běží v **testovacím režimu**. Výchozí nastavení je TRUE.

```php
<?php
$skautis = SkautIS::getInstance("moje-application-id", $isTestMode = TRUE);
```

### Nette
Pro připojení knihovny do Nette existuje rozšíření [skaut/SkautisNette](https://github.com/skaut/skautisNette), které ji při instalaci přes composer celou připojí a přidá debugovací panel.

## Přihlášení
```php
<?php
echo '<a href="'.$skautis->getLoginUrl();.'"> Prihlasit se</a>'; //Vypise odkaz pro prihlaseni do skautisu
```

Po úspěšném přihlášení jsme přesměrováni na předem nastavenou adresu (viz. [nápověda](http://is.skaut.cz/napoveda/programatori.3-naprogramovani-obslouzeni-uspesneho-prihlaseni-a-odhlaseni.ashx#Hodnoty_zaslan%C3%A9_webov%C3%A9_str%C3%A1nce_po_%C3%BAsp%C4%9B%C5%A1n%C3%A9m_p%C5%99ihl%C3%A1%C5%A1en%C3%AD_u%C5%BEivatele_0)) a pošle nám údaje přes $_POST
(ID_Login, ID_Role, ID_Unit). Ty nastavíme knihovně, která si je zapamatuje.

```php
<?php
$skautis->setToken($_POST['skautIS_Token']);  
$skautis->setRoleId($_POST['skautIS_IDRole']);  
$skautis->setUnitId($_POST['skautIS_IDUnit']);
```

nebo 

```php
<?php
$skautis->setLoginData($_POST['skautIS_Token'], $_POST['skautIS_IDRole'], $_POST['skautIS_IDUnit']);
```

## Získání dat ze skautisu
```php
<?php
$data = $skautis->webova_sluzba->funkce(array("nazev_atributu"=>"hodnota_atributu", ...));
```

nebo při použití extension pro Nette

```php
$this->context->skautis->webova_sluzba->funkce(array("nazev_atributu"=>"hodnota_atributu", ...));
```

Pro přístup k webové službě skautisu můžeme použít její plný název nebo alias.

Již předdefinované aliasy:

* usr => user => UserManagement
* org => OrganizationUnit
* app => ApplicationManagement
* event => events => Events

Argumenty zadáváme v asociativním poli "nazev_atributu"=>"hodnota_atributu". Pokud je atribut ID_Login nastaven, je automaticky přidán ke všem požadavkům.

```php
<?php
$data = $skautis->user->UserDetail(array("ID"=>1940)); //1940 je ID uzivatele okres blansko
```
