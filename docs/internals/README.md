# Internals
Je očekávatelné že je potřeba přizpůsobit knihovnu aplikaci nebo frameworku a nebo přidat nějakou funkcionalitu. Toho se dá snadno dosáhnout pomocí přizpůsobení vnitřností knihovny které se používají při konfiguraci bez nutnosti upravovat použití knihovny.

## Skautis
Je _lepidlo_ spojujici dalsi tridy do funkcniho celku. To je trida se kterou se nejčastěji pracuje.

## SessionAdapter
Je obal pro implementaci session. Vice o [adapter patternu](https://github.com/domnikl/DesignPatternsPHP/tree/master/Structural/Adapter)
Příklad [implementace pro Nette](./session_adapter.md)

## WebService
WebService je objekt webové služby. Který lze rozšířit třeba o [logování](./web_service.md).

## WebServiceFactory
Slouží k vytváření WebService objektu. Je přitomen pro možné úpravy WebService pro projekty. Více o [abstract factory patternu](https://github.com/domnikl/DesignPatternsPHP/tree/master/Creational/AbstractFactory)
Například může předávat službu pro [logování](./web_service_factory.md)

## WebService Decorator
Pro úpravu WebService doporučujeme použít [decorator pattern](https://github.com/domnikl/DesignPatternsPHP/tree/master/Structural/Decorator). Který umožňuje skládání nových funkcí. Například logování a cache.

### CacheDecorator
Knihovna nabízí připravený [dekorátor pro cachovaní requestu](./cache_decorator.md).
