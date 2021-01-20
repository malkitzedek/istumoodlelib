<?php
/**
 * С помощью Небес!
 *
 * @copyright   2020 Novostruev Ivan - rusmatrix@gmail.com
 */

namespace Novostruev\istu;

class Config
{
    /**
     * @param string $configName
     * @return string
     */
    public static function getFilename(string $configName): string
    {
        global $CFG;
        return $CFG->dirroot . '/istu/configs/' . $configName . '.php';
    }
}
