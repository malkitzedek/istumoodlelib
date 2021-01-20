<?php
/**
 * С помощью Небес!
 *
 * @copyright   2020 Novostruev Ivan - rusmatrix@gmail.com
 */

namespace Novostruev\istu;

abstract class Request
{
    /**
     * @param string $fullurl
     * @param array $params
     * @return string
     */
    abstract public static function execute(string $fullurl, array $params=[]): string;
}
