<?php

declare(strict_types=1);


namespace Skautis\Wsdl;


class WebServiceAlias
{
  /**
   * Aliasy webových služeb pro rychlý přístup
   *
   * @var string[]
   */
  private const ALIASES = [
    'user' => 'UserManagement',
    'usr' => 'UserManagement',
    'org' => 'OrganizationUnit',
    'app' => 'ApplicationManagement',
    'event' => 'Events',
    'events' => 'Events',
  ];

  public static function resolveAlias(string $alias): string {
    $alias = strtolower($alias);

    if (!array_key_exists($alias, self::ALIASES)) {
      throw new WebServiceAliasNotFoundException($alias);
    }

    return self::ALIASES[$alias];
  }
}