# Jak se zúčastnit vývoje

## Jsem začátečník a nevím, co dělat
Ideální první krok je otevřít issue a napsat, co byste chtěli změnit: co, proč a jestli chcete změnu provést sami,
nebo ji jen navrhujete. Issue založíte [tady](https://github.com/skaut/Skautis/issues/new).

## Jsem pokročilý
Začněte tím, že [vytvoříte issue](https://github.com/skaut/Skautis/issues/new) jako začátečník, a potom pokračujte
jako programátor; v issue se ptejte na věci, které nejsou jasné.

## Jsem programátor a vím, co dělám
Předpokládáme znalost PHP, Gitu, GitHubu a ideálně PHPUnitu.

* Forkněte tento repozitář a naklonujte si ho.
* Napište testy (pokud nevíte, o čem je řeč, tento řádek přeskočte).
* Nakódujte změny. Komentáře pište jen tam, kde není zřejmé proč.
* Doplňte testy.
* Přidejte informace o změnách do `CHANGELOG.md`.
* Pokud jde o významnou změnu nebo novou funkci, upravte dokumentaci ve složce `docs`.
* Spusťte `make ci` (nebo `composer ci`, máte-li PHP lokálně): lint, coding standard, PHPStan a PHPUnit musí projít
  na PHP 8.4 i 8.5 (`make ci PHP=8.5`).
* Pošlete pull request.
