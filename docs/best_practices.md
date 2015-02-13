#Best practices
Knihovna ve verzi 2.x je poměrně komplexní stvoření a pro co nejlepší využití doporučujeme následující.

##Konfigurace na jednom místě
Protože konfigurace je velmi složitá jak je vidět v [kapitole o konfiguraci](./konfigurace.md) je dobré ji mít na jednom místě a sdílet ji pro celou aplikaci.
Může se jednat o samostatný PHP soubor includovany kam je potřeba. Nebo za využití [dependency injection containeru](http://doc.nette.org/cs/2.3/dependency-injection).


##Kontrola přihlášení
Pokud se chystá aplikace provádět dotazy na server je vhodné zkontrolovat zda je uživatel přihlášen s vynucením ověření. Tím se předejde výjímce pro neplatné přihlášení a prodlouží přihlášení o 30 minut.

```PHP
$skautisUser = $skautis->getUser();

//Lokální kontrola podle času odhlášení
if (!$skautisUser->isLoggedIn()) {
    echo "Je potřeba se první přihlásit.";
}

//Ověření přihlášení na serveru SkautISu
if (!$skautisUser->isLoggedIn(true)) {
    echo "Je potřeba se první přihlásit.";
}
```

###Rychlost
Vynucení ověření ovšem posílá dotaz na server, což nějakou dobu trvá. Proto není vhodné ho vyžadovat vícekrát než jednou.
Vhodné řešení by mohlo být namespacovat url ktera pracuji se skautisem my-web.cz/skautis/zbytek_url a použít middleware který by ověřoval přihlášení.


##Používání komponent
Knihovna se skladá z mnoha tříd. Mnoho z nich je považováno za interní a po konfiguraci knihvony na ně není dobré sahat.
Spolehlivé a dopředně kompatibilní je používat ``Skautis\Skautis``, ``Skautis\Config``, ``Skautis\User`` a ``Skautis\Wsdl\WebServiceInterface``.

##Chytání výjimek
Knihovna používá pro všechny výjimky interface ``Skautis\Exception``.
```PHP
try {
    //nejaka práce se skautisem
}
catch (Skautis\Exception $e) {
    //Problem se skautisem.
}
catch (Exception $e) {
    //Error 500, neco neocekavaneho se stalo
}
```
