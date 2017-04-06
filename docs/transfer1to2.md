# Přechod z verze 1.x na 2.x
Zde jsou popsané nejčastější problémy vzniklé nekompatibilitou mezi verzí 1.x a 2.x.

## Změna velikosti písmen ze SkautIS na Skautis

Třídy, které dříve obsahovali SkautIS se nyní jmenují s malým "is", tedy Skautis namísto SkautIS.

## get...Id()
Funkce $skautis->getNecoId() (getLoginId(), getRoleId(), getUnitId()) se přesunuly do třídy [User](/src/User.php), kterou získáme pomocí getUser(). Tedy přístup k nim je přes $skautis->getUser()->getNecoId().
