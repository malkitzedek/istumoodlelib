<?php
/**
 * С помощью Небес!
 *
 * @copyright   2020 Novostruev Ivan - rusmatrix@gmail.com
 */

namespace Novostruev\istu;

use ErrorException;

require_once($CFG->dirroot.'/istu/classes/Request.php');

class AuthRequest extends Request
{
    /**
     * @param $fullurl
     * @param array $params
     * @return string
     * @throws ErrorException
     */
    public static function execute(string $fullurl, array $params=[]): string
    {
        $postFields = $params;
        $postFields["remember_me"] = true;
        $postFieldsToString = json_encode($postFields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullurl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFieldsToString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($postFieldsToString))
        );

        $result = curl_exec($ch);
        $curl_errno = curl_errno($ch);
        curl_close($ch);

        if ($curl_errno > 0 || !$result) {
            throw new ErrorException('Запрос к сервису не выполнен');
        }
        return $result;
    }
}
