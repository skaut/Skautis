#Prace se skautisem

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
