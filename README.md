[![Build Status](https://travis-ci.org/skaut/Skautis.svg?branch=2.x)](https://travis-ci.org/skaut/Skautis) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/skaut/Skautis/badges/quality-score.png?b=2.x)](https://scrutinizer-ci.com/g/skaut/Skautis/?branch=2.x) [![Code Coverage](https://scrutinizer-ci.com/g/skaut/Skautis/badges/coverage.png?b=2.x)](https://scrutinizer-ci.com/g/skaut/Skautis/?branch=2.x)
[![Latest Stable Version](https://poser.pugx.org/skautis/skautis/v/stable.svg)](https://packagist.org/packages/skautis/skautis) [![Latest Unstable Version](https://poser.pugx.org/skautis/skautis/v/unstable.svg)](https://packagist.org/packages/skautis/skautis) [![License](https://poser.pugx.org/skautis/skautis/license.svg)](https://packagist.org/packages/skautis/skautis)

# SkautIS
PHP knihovna pro připojení do [Skautisu](https://is.skaut.cz/)

## Ukázka
```PHP
//získání podřízených jednotek k té kde jsem přihlášen rolí
$myUnitId = $skautis->getUser()->getUnitId();
$skautis->org->unitAll(array("ID_UnitParent"=>$myUnitId))
```

## Navod na pouziti
Podrobný návoud v [dokumentaci](docs/README.md).


## Požadavky
PHP 5.6 a novější. Detaily v [composer.json](./composer.json)
