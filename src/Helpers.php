<?php

namespace Skautis;

use Skautis\Exception\InvalidArgumentException;
use Skautis\Exception\StaticClassException;


/**
 * @author Petr Morávek <petr@pada.cz>
 */
final class Helpers
{

    /**
     * @throws StaticClassException
     */
    final public function __construct()
    {
        throw new StaticClassException;
    }

    /**
     * Parsuje pole dat zaslaných skautISem (například $_SESSION)
     *
     * @param array $data
     * @return array
     * @throws InvalidArgumentException pokud se nepodaří naparsovat datum
     */
    public static function parseLoginData(array $data)
    {
        $loginData = [];
        $loginData[User::ID_LOGIN] = isset($data['skautIS_Token']) ? $data['skautIS_Token'] : null;
        $loginData[User::ID_ROLE] = isset($data['skautIS_IDRole']) ? (int) $data['skautIS_IDRole'] : null;
        $loginData[User::ID_UNIT] = isset($data['skautIS_IDUnit']) ? (int) $data['skautIS_IDUnit'] : null;

        if (isset($data['skautIS_DateLogout'])) {
            $tz = new \DateTimeZone('Europe/Prague');
            $logoutDate = \DateTime::createFromFormat('j. n. Y H:i:s', $data['skautIS_DateLogout'], $tz);
            if ($logoutDate === false) {
                throw new InvalidArgumentException('Could not parse logout date.');
            }
            $loginData[User::LOGOUT_DATE] = $logoutDate;
        } else {
            $loginData[User::LOGOUT_DATE] = null;
        }

        return $loginData;
    }

}
