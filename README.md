![GitHub Workflow Status](https://img.shields.io/github/workflow/status/skaut/Skautis/main)
[![Coverage Status](https://coveralls.io/repos/github/skaut/Skautis/badge.svg?branch=3.x)](https://coveralls.io/github/skaut/Skautis?branch=3.x)
[![Latest Stable Version](https://poser.pugx.org/skautis/skautis/v/stable.svg)](https://packagist.org/packages/skautis/skautis)
[![Latest Unstable Version](https://poser.pugx.org/skautis/skautis/v/unstable.svg)](https://packagist.org/packages/skautis/skautis)
[![License](https://poser.pugx.org/skautis/skautis/license.svg)](https://packagist.org/packages/skautis/skautis)

# SkautIS
PHP knihovna pro připojení do [Skautisu](https://is.skaut.cz/)

## Ukázka
```PHP
//získání podřízených jednotek k té kde jsem přihlášen rolí
$myUnitId = $skautis->getUser()->getUnitId();
$skautis->org->unitAll(array("ID_UnitParent"=>$myUnitId))
```

## Návod na použití
Podrobný návod v [dokumentaci](docs/README.md).

## Požadavky
PHP 7.1 a novější. Detaily v [composer.json](./composer.json)
