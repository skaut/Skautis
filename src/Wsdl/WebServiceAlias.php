<?php

declare(strict_types=1);

namespace Skaut\Skautis\Wsdl;

/**
 * Short aliases of the web services: $skautis->org instead of $skautis->OrganizationUnit.
 */
final class WebServiceAlias
{
    private const array ALIASES = [
        'user' => WebServiceName::USER_MANAGEMENT,
        'usr' => WebServiceName::USER_MANAGEMENT,
        'org' => WebServiceName::ORGANIZATION_UNIT,
        'app' => WebServiceName::APPLICATION_MANAGEMENT,
        'event' => WebServiceName::EVENTS,
        'events' => WebServiceName::EVENTS,
    ];

    /**
     * @throws WebServiceAliasNotFoundException
     */
    public static function resolveAlias(string $alias): string
    {
        $alias = strtolower($alias);

        if (! \array_key_exists($alias, self::ALIASES)) {
            throw new WebServiceAliasNotFoundException($alias);
        }

        return self::ALIASES[$alias];
    }
}
