[![main](https://github.com/skaut/Skautis/actions/workflows/main.yml/badge.svg)](https://github.com/skaut/Skautis/actions/workflows/main.yml)
[![License](https://img.shields.io/badge/license-BSD--3--Clause-blue.svg)](./LICENSE)

# SkautIS
PHP knihovna pro připojení do [Skautisu](https://is.skaut.cz/)

## Ukázka
```PHP
//získání podřízených jednotek k té kde jsem přihlášen rolí
$myUnitId = $skautis->getUser()->getUnitId();
$skautis->org->unitAll(['ID_UnitParent' => $myUnitId]);
```

## Návod na použití
Podrobný návod v [dokumentaci](docs/README.md). Přehled změn ve [CHANGELOG.md](CHANGELOG.md).

## Požadavky
PHP 8.4 a novější s rozšířením `soap`. Detaily v [composer.json](./composer.json).

## Vývoj
Na počítači stačí Docker, PHP ani Composer nejsou potřeba:

```bash
make build            # vývojový obraz (PHP=8.5 pro druhou verzi)
make install          # composer install
make ci               # lint, coding standard, PHPStan, PHPUnit
```

`make help` vypíše všechny cíle. Lokální nastavení, například `DOCKER_ROOTLESS=1` pro rootless Docker,
patří do git-ignorovaného souboru `make.local`.
