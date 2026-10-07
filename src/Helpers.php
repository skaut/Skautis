<?php

declare(strict_types=1);

namespace Skaut\Skautis;

use DateTimeImmutable;
use DateTimeZone;

/**
 * @author Petr Morávek <petr@pada.cz>
 */
final class Helpers
{
    private function __construct()
    {
    }

    /**
     * Parses the fields skautIS posts back after login (skautIS_Token, skautIS_IDRole, skautIS_IDUnit, skautIS_DateLogout).
     *
     * @param array<string, mixed> $data
     *
     * @return array{ID_Login: string|null, ID_Role: int|null, ID_Unit: int|null, LOGOUT_Date: DateTimeImmutable|null}
     *
     * @throws UnexpectedValueException when the logout date cannot be parsed
     */
    public static function parseLoginData(array $data): array
    {
        $logoutDate = null;
        if (isset($data['skautIS_DateLogout'])) {
            $dateText = self::toString($data['skautIS_DateLogout']);
            $logoutDate = DateTimeImmutable::createFromFormat('j. n. Y H:i:s', $dateText, new DateTimeZone('Europe/Prague'));
            if ($logoutDate === false) {
                throw new UnexpectedValueException("Could not parse logout date '$dateText'.");
            }
        }

        return [
            User::ID_LOGIN => isset($data['skautIS_Token']) ? self::toString($data['skautIS_Token']) : null,
            User::ID_ROLE => isset($data['skautIS_IDRole']) ? self::toInt($data['skautIS_IDRole']) : null,
            User::ID_UNIT => isset($data['skautIS_IDUnit']) ? self::toInt($data['skautIS_IDUnit']) : null,
            User::LOGOUT_DATE => $logoutDate,
        ];
    }

    private static function toString(mixed $value): string
    {
        return \is_scalar($value) ? (string) $value : '';
    }

    private static function toInt(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }
}
