# WebService
Předpokládejme, že chceme logovat každý požadavek na skautIS a máme třídu ``Logger``.

## Dekorátor
Dekorátor dostane objekt, který obaluje, a sám implementuje jeho rozhraní; dá se tedy používat místo něj.

## Implementace
```PHP
use Skaut\Skautis\Wsdl\Decorator\AbstractDecorator;
use Skaut\Skautis\Wsdl\WebServiceInterface;

final class LoggerDecorator extends AbstractDecorator
{
    public function __construct(WebServiceInterface $webService, private readonly Logger $logger)
    {
        $this->webService = $webService; // protected vlastnost rodiče
    }

    public function call(string $functionName, array $arguments = []): mixed
    {
        try {
            $result = $this->webService->call($functionName, $arguments);
            $this->logger->info("Function '$functionName' succeeded");

            return $result;
        } catch (\Throwable $exception) {
            $this->logger->error("Function '$functionName' failed: $exception");

            throw $exception;
        }
    }
}
```

Použití; pro správné zapojení do knihovny se podívejte na [WebServiceFactory](./web_service_factory.md):
```PHP
$webService = new LoggerDecorator($skautis->UserManagement, new Logger());

// dál se používá jako obyčejná webová služba
$webService->UserDetail();
```

Pro pouhé logování ale dekorátor nepotřebujete: knihovna vysílá PSR-14 události ``RequestPreEvent``,
``RequestPostEvent`` a ``RequestFailEvent`` (viz ``WsdlManager::setEventDispatcher()``).
