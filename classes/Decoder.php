<?php
/**
 * С помощью Небес!
 *
 * @copyright   2020 Novostruev Ivan - rusmatrix@gmail.com
 */

namespace Novostruev\istu;

use ErrorException;

class Decoder
{
    /**
     * @param $data
     * @param string $format
     * @return mixed
     * @throws ErrorException
     */
    public static function decode($data, $format='json')
    {
        switch ($format) {
            case 'json':
                return self::getDataFromJson($data);
            default:
                throw new ErrorException('Неизвестный формат данных');
        }
    }

    /**
     * @param $data
     * @return mixed
     * @throws ErrorException
     */
    private static function getDataFromJson($data)
    {
        $data = json_decode($data);
        if ($data === null) {
            throw new ErrorException('Не удалось преобразовать данные в json');
        }
        return $data;
    }
}
