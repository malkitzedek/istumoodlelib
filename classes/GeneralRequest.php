<?php
/**
 * С помощью Небес!
 *
 * @copyright   2020 Novostruev Ivan - rusmatrix@gmail.com
 */

namespace Novostruev\istu;

use ErrorException;

class GeneralRequest extends Request
{
    /**
     * @param $fullurl
     * @param array $params
     * @return string
     * @throws ErrorException
     */
    public static function execute($fullurl, array $params=[]): string
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $fullurl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3600);

        $result = curl_exec($ch);
        $curl_errno = curl_errno($ch);
        curl_close($ch);

        if ($curl_errno > 0 || !$result) {
            throw new ErrorException('Запрос к сервису не выполнен');
        }
        return $result;
    }
}
