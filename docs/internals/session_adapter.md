# SessionAdapter

Velmi jednoduše se může stát že aplikace kterou píšeme používá pro ``session`` nějaký objekt místo defaultního ``$_SESSION``. V takovém případě je potřeba napsat adaptér abz knihovna mohla pracovat se session.
Předpákládejme že potřebujeme pracovat s Nette session.

## Adapter pattern
[Adapter patter](https://github.com/domnikl/DesignPatternsPHP/tree/master/Structural/Adapter) je způsob kterým sjednocujeme interface.

## Implementace
```PHP
class NetteSessionAdapter implements \Skaut\Skautis\SessionAdapter\AdapterInterface
{
    //Nette session objekt
    protected $sessionSection;

    public function __construct(Nette\Http\Session $session)
    {
        //Namespacuje session kvůli kolizi
        $this->sessionSection = $session->getSection(__CLASS__);
    }

    //Funkce pro nastavení dat do session
    public function set($name, $object): void
    {
        $this->sessionSection->$name = $object;
    }

    //Funkce pro ověření existence dat v session
    public function has($name): bool
    {
        return isset($this->sessionSection->$name);
    }

    //Funkce pro získání dat ze session
    public function get($name)
    {
        return $this->sessionSection->$name;
    }
}
```
