#WebService
Předpokládejme že chceme logovat každý request na SkautIS a máme třídu ``Logger``.

##DecoratorPattern
Decorator funguje tak že dostane objekt který dekoruje a on sám implementuje jeho interface a dá se tedy nadále používat místo něj.

##Implementace
```PHP
class LoggerDecorator extends Skautis\Wsdl\Decorator\AbstractDecorator
{
    protected $logger;

    public function __construct($webService, Logger $logger) {
        $this->webService = $webService; //protected $this->webService od rodiče
        $this->logger = $logger;
    }

    public function call($functionName, array $arguments = [])
    {
        try {
            $this->webService->call($functionName, $arguments);
            $this->logger->info("Function '$functionName' with $arguments");
        }
        catch (\Exception $e) {
            $this->logger->error("Function '$functionName' with $arguments and exception $e");
        }
    }
}
```

Takhle nějak by vypadalo použití. Pro správnou implementaci se podívejte na [WebServiceFactory](./web_service_factory.md)
```PHP
$logger = new Logger();
$webService = $skautis->UserManagement;

//Obalí objekt
$webService = new LoggerDecorator($webService, $logger);

//Bez jakékoliv změny použije WebService.
$webService->UserDetail(['ID_UnitParent' => '24404']);
```
