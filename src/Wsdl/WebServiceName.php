<?php

declare(strict_types=1);


namespace Skautis\Wsdl;


use ReflectionClass;

/**
 * Dostupné webové služby SkautISu
 */
abstract class WebServiceName
{

  public const APPLICATION_MANAGEMENT = 'ApplicationManagement';

  public const CONTENT_MANAGEMENT = 'ContentManagement';

  public const DOCUMENT_STORAGE = 'DocumentStorage';

  public const EVALUATION = 'Evaluation';

  public const EVENTS = 'Events';

  public const EXPORTS = 'Exports';

  public const GOOGLE_APPS = 'GoogleApps';

  public const GRANTS = 'Grants';

  public const INSURANCE = 'Insurance';

  public const JOURNAL = 'Journal';

  public const MATERIAL = 'Material';

  public const MESSAGE = 'Message';

  public const ORGANIZATION_UNIT = 'OrganizationUnit';

  public const POWER = 'Power';

  public const REPORTS = 'Reports';

  public const SUMMARY = 'Summary';

  public const TASK = 'Task';

  public const TELEPHONY = 'Telephony';

  public const USER_MANAGEMENT = 'UserManagement';

  public const VIVANT = 'Vivant';

  public const WELCOME = 'Welcome';

  /**
   * @var string[]
   */
  private static $cachedConstants = [];


  /**
   * @return string[]
   */
  private static function getConstants(): array
  {
    if (!self::$cachedConstants) {
      $reflect = new ReflectionClass(static::class);
      self::$cachedConstants = $reflect->getConstants();
    }

    return self::$cachedConstants;
  }

  public static function isValidServiceName(
    string $name
  ): bool {
    return in_array($name, self::getConstants(), true);
  }
}