# WebServiceFactory

Víme že Skautis vytváří objekty pro webové služby automaticky. WebServiceFactory dává způsob jak toto vytváření upravit.
Předpokládejme že chceme logovat všechny request na Skautis a máme připravený [web service decorator](./web_service.md)

## Implementace
```PHP
class LoggingWebServiceFactory implements \Skaut\Skautis\Wsdl\WebServiceFactoryInterface
{
    //Logger pro logovani vsech requestu na SkautIS
    protected $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }


    public function createWebService($url, array $options)
    {
        $webService = new \Skaut\Skautis\Wsdl\WebService($url, $options);
        $webService = new LoggerDecorator($webService, $this->logger);
        return $webService;
    }
}
```

[V konfiguraci](docs/konfigurace.md) nahradíme defaultní WebServiceFactory na nasi novou factory. A to je vše. Logování funguje bez jediné změny v aplikačním kódu.
