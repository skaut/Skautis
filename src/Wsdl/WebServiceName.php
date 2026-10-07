<?php

declare(strict_types=1);

namespace Skaut\Skautis\Wsdl;

/**
 * Names of the skautIS web services.
 */
abstract class WebServiceName
{
    public const string APPLICATION_MANAGEMENT = 'ApplicationManagement';
    public const string CONTENT_MANAGEMENT = 'ContentManagement';
    public const string DOCUMENT_STORAGE = 'DocumentStorage';
    public const string EVALUATION = 'Evaluation';
    public const string EVENTS = 'Events';
    public const string EXPORTS = 'Exports';
    public const string GOOGLE_APPS = 'GoogleApps';
    public const string GRANTS = 'Grants';
    public const string INSURANCE = 'Insurance';
    public const string JOURNAL = 'Journal';
    public const string MATERIAL = 'Material';
    public const string MESSAGE = 'Message';
    public const string ORGANIZATION_UNIT = 'OrganizationUnit';
    public const string POWER = 'Power';
    public const string REPORTS = 'Reports';
    public const string SUMMARY = 'Summary';
    public const string TASK = 'Task';
    public const string TELEPHONY = 'Telephony';
    public const string USER_MANAGEMENT = 'UserManagement';
    public const string VIVANT = 'Vivant';
    public const string WELCOME = 'Welcome';

    private const array ALL = [
        self::APPLICATION_MANAGEMENT,
        self::CONTENT_MANAGEMENT,
        self::DOCUMENT_STORAGE,
        self::EVALUATION,
        self::EVENTS,
        self::EXPORTS,
        self::GOOGLE_APPS,
        self::GRANTS,
        self::INSURANCE,
        self::JOURNAL,
        self::MATERIAL,
        self::MESSAGE,
        self::ORGANIZATION_UNIT,
        self::POWER,
        self::REPORTS,
        self::SUMMARY,
        self::TASK,
        self::TELEPHONY,
        self::USER_MANAGEMENT,
        self::VIVANT,
        self::WELCOME,
    ];

    /**
     * @return list<string>
     */
    public static function getAll(): array
    {
        return self::ALL;
    }

    public static function isValidServiceName(string $name): bool
    {
        return \in_array($name, self::ALL, true);
    }
}
