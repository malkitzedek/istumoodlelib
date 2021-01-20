<?php
/**
 * С помощью Небес!
 *
 * @copyright   2020 Novostruev Ivan - rusmatrix@gmail.com
 */

namespace Novostruev\istu;

use dml_exception;
use ErrorException;

require_once($CFG->dirroot.'/istu/classes/Config.php');

class Profile
{
    const CONFIG_NAME = 'profile_parameters';

    /**
     * Массив значений поля 'id' соответсвующих значениям:
     * 'infoperson' поля 'shortname' таблицы 'user_info_field'
     * 'infostudent' поля 'shortname' таблицы 'user_info_field'
     *
     * @var int[]
     */
    private static $fieldIds = [];

    /**
     * @var DBI Объект для взаимодействия с базой данных
     */
    public static $db;

    /**
     * Profile constructor.
     * @param DBI $db
     * @throws ErrorException
     */
    public function __construct(DBI $db)
    {
        self::init();
        self::$db = $db;
    }

    /**
     * Инициализирует необходимые переменные
     *
     * @throws ErrorException
     */
    private static function init()
    {
        $configFile = Config::getFilename(self::CONFIG_NAME);
        if (!file_exists($configFile)) {
            throw new ErrorException('Файл конфигурации профиля не найден');
        }

        if (empty(self::$fieldIds)) {
            self::$fieldIds = require_once($configFile);
        }
    }

    /**
     * @param string $fieldName
     * @param int $userId
     * @param string|null $data
     * @throws dml_exception
     */
    public static function fillField(string $fieldName, int $userId, string $data)
    {
        if ($fieldId = self::getFieldIdByName($fieldName)) {
            self::updateField($userId, $fieldId, $data);
        }
    }

    /**
     * @param string $fieldName
     * @return int|null
     */
    private static function getFieldIdByName(string $fieldName): ?int
    {
        return self::$fieldIds[$fieldName] ?? null;
    }

    /**
     * Обновляет соответствующую запись с информацией
     * о преподавателе/студенте в таблице 'user_info_data'
     *
     * @param int $userId
     * @param int $fieldId
     * @param string|null $html
     * @throws dml_exception
     */
    private static function updateField(int $userId, int $fieldId, string $html=null)
    {
        // Проверка, есть ли уже запись с данными о пользователе в таблице 'user_info_data'
        if ($userInfoDataRecord = self::$db->getUserInfoDataRecord($userId, $fieldId)) {
            // Случай, когда запись уже есть
            self::$db->updateUserInfoDataRecord($userInfoDataRecord->id, $html);
        } else {
            // Случай, когда записи ещё нет и её нужно создать
            self::$db->insertUserInfoDataRecord($userId, $fieldId, $html);
        }
    }
}
