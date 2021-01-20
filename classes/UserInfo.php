<?php
/**
 * С помощью Небес!
 *
 * @copyright   2020 Novostruev Ivan - rusmatrix@gmail.com
 */

namespace Novostruev\istu;

use ErrorException;

require_once($CFG->dirroot.'/istu/classes/Config.php');

class UserInfo
{
    const CONFIG_NAME = 'user_info_parameters';

    /**
     * @var array
     */
    private static $fieldMap = [];

    /**
     * @var array Массив типов полей профиля пользователя
     */
    private static $scheme = [];

    /**
     * Возвращает массив c данными о персоне, либо о студенте
     *
     * @param string $type
     * @param array $datas
     * @return string
     * @throws ErrorException
     */
    public static function get(string $type, array $datas): string
    {
        self::init();
        $scheme = self::getScheme($type);

        $info = [];
        foreach ($datas as $key => $data) {
            foreach ($scheme as $field) {
                if (isset($data->$field)) {
                    $info[$key][$field] = $data->$field;
                }
            }
        }
        return self::convertToHtml($info);
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
            throw new ErrorException('Файл конфигурации UserInfo не найден');
        }

        $configs = require_once($configFile);

        if (empty(self::$fieldMap)) {
            self::$fieldMap = $configs['fields_translation'];
        }

        if (empty(self::$scheme)) {
            self::$scheme = $configs['info_scheme'];
        }
    }

    /**
     * @param string $type
     * @return string[]
     */
    private static function getScheme(string $type): array
    {
        return self::$scheme[$type] ?? [];
    }

    /**
     * Обрабатывает массив и возвращает отформатированный html-код в виде строки
     *
     * @param array $info
     * @return string Отформатированная строка
     */
    private static function convertToHtml(array $info): string
    {
        $html = '';
        $cnt = 0;
        foreach ($info as $elements) {
            $html .= "<dl>";
            foreach ($elements as $key => $value) {
                if (self::$fieldMap[$key]) {
                    $html .= "<dt>" . self::$fieldMap[$key] . "</dt>";
                    $html .= "<dd>$value</dd>";
                    $cnt++;
                }
            }
            $html .= "</dl>";
        }
        return $cnt ? $html : '';
    }
}
