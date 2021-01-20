<?php
/**
 * С помощью Небес!
 *
 * @copyright   2020 Novostruev Ivan - rusmatrix@gmail.com
 */

namespace Novostruev\istu\services;

use ErrorException;
use Novostruev\istu\{Decoder, Request};

require_once($CFG->dirroot.'/istu/classes/Decoder.php');

abstract class Service
{
    const DEFAULT_EXCHANGE_FORMAT = 'json';

    const ALLOWED_DATA_TYPE = ['array', 'object'];

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string Требуемый тип данных. Берется из файла конфига.
     */
    protected $dataType;

    /**
     * @var string Фактический тип данных
     */
    protected $actualDataType;

    /**
     * @var string
     */
    protected $exchangeFormat;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Service constructor.
     * @param array $params
     * @param Request $request
     * @throws ErrorException
     */
    public function __construct(array $params, Request $request)
    {
        $this->request = $request;

        $this->setUrl($params);
        $this->setDataType($params);
        $this->setExchangeFormat($params);
    }

    /**
     * @param array $params
     * @return mixed
     * @throws ErrorException
     */
    public function getData(array $params=[])
    {
        $data = Decoder::decode(
            $this->request::execute($this->url, $params),
            $this->exchangeFormat
        );

        if (!$this->checkDataType($data)) {
            throw new ErrorException($this->getErrorString());
        }
        return $data;
    }

    /**
     * @param array $params
     * @throws ErrorException
     */
    public function setUrl(array $params): void
    {
        if (empty($params['url'])) {
            throw new ErrorException('URL-адрес сервиса не определен');
        }
        $this->url = trim((string) $params['url']);
    }

    /**
     * @param array $params
     * @throws ErrorException
     */
    public function setDataType(array $params): void
    {
        if (empty($params['data_type'])) {
            throw new ErrorException('Тип данных, получаемых от сервиса, не определен');
        }

        $dataType = trim((string) $params['data_type']);
        if (!in_array($dataType, self::ALLOWED_DATA_TYPE)) {
            throw new ErrorException('Недопустимый тип данных');
        }
        $this->dataType = $dataType;
    }

    /**
     * @param array $params
     */
    public function setExchangeFormat(array $params): void
    {
        $this->exchangeFormat = !empty($params['exchange_format'])
            ? trim((string) $params['exchange_format'])
            : self::DEFAULT_EXCHANGE_FORMAT;
    }

    /**
     * @param $data
     * @return bool
     */
    public function checkDataType($data): bool
    {
        $this->actualDataType = gettype($data);
        switch ($this->dataType) {
            case 'array':
                return is_array($data);
            case 'object':
                return is_object($data);
            default:
                return false;
        }
    }

    /**
     * @return string
     */
    public function getErrorString(): string
    {
        return "Полученные данные имеют некорректный тип. " .
            "Требуемый тип: {$this->dataType}. Фактический тип: {$this->actualDataType}.";
    }
}
