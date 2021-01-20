<?php
/**
 * С помощью Небес!
 *
 * @copyright   2020 Novostruev Ivan - rusmatrix@gmail.com
 */

namespace Novostruev\istu;

class Logger
{
    const LOG_DIR = '/istu_logs';

    const DEFAULT_FILENAME = 'general_log.txt';

    /**
     * @var string Путь к корневому каталогу файлов Мудла
     */
    private static $dataRoot;

    /**
     * Logger constructor.
     * @param string $dataRoot
     */
    public function __construct(string $dataRoot)
    {
        self::$dataRoot = $dataRoot;

        if (!file_exists(self::$dataRoot . self::LOG_DIR)) {
            mkdir(self::$dataRoot . self::LOG_DIR, 0777, true);
        }
    }

    /**
     * Метод пишет логи в файл на диске
     * В случае если лог-файла на диске не существует,
     * он автоматически создаётся функцией fopen(),
     * а в случае невозможности создать файл, генерируется ошибка
     *
     * @param string $text
     * @param string|null $filename Name of log file
     *
     * @return void - пустое значение
     */
    public function log(string $text, string $filename=null)
    {
        $fh = fopen($this->getFilename($filename), 'a');
        flock($fh, LOCK_EX);
        fwrite($fh, $this->getContent($text));
        flock($fh, LOCK_UN);
        fclose($fh);
    }

    /**
     * @param string|null $filename
     * @return string
     */
    private function getFilename(string $filename=null)
    {
        $filename = $filename ?: self::DEFAULT_FILENAME;
        return self::$dataRoot . self::LOG_DIR . '/' . $filename;
    }

    /**
     * @param string $text
     * @return string
     */
    private function getContent(string $text): string
    {
        return date('Y-m-d H:i:s') . ": " . $text . "\n";
    }
}
