<?php
/**
 * С помощью Небес!
 *
 * @copyright   2020 Novostruev Ivan - rusmatrix@gmail.com
 */

namespace Novostruev\istu;

use stdClass;

class Auth
{
    /**
     * Имена лог-файлов:
     * сервиса аутентификации [успешные попытки]
     * сервиса аутентификации [сбои]
     * сервисов получения данных [успешные попытки]
     * сервисов получения данных [сбои]
     */
    const LOG_FILENAME = [
        'auth_success' => 'auth_success_log.txt',
        'auth_failure' => 'auth_failure_log.txt',
        'data_success' => 'data_success_log.txt',
        'data_failure' => 'data_failure_log.txt'
    ];

    /**
     * @var string Логин пользователя, введённый при входе в Систему
     */
    private $username;

    /**
     * @var string Пароль пользователя, введённый при входе в Систему
     */
    private $password;

    /**
     * @var null|string Токен, полученный от сервиса аутентификации
     */
    private $token = null;

    /**
     * @var null|integer Персональный идентификатор пользователя,
     * полученный от сервиса аутентификации ИжГТУ
     */
    private $idNumber = null;

    /**
     * @var bool
     */
    public $isVerify = false;

    /**
     * Auth constructor.
     * @param $data
     */
    public function __construct(stdClass $data)
    {
        $this->setUsername($data);
        $this->setPassword($data);
        $this->setToken($data);
        $this->setIdNumber($data);
        $this->setIsVerify($data);
    }

    /**
     * @param $data
     */
    public function setUsername(stdClass $data): void
    {
        $this->username = $data->username;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param $data
     */
    public function setPassword(stdClass $data): void
    {
        $this->password = $data->password;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param $data
     */
    public function setToken(stdClass $data): void
    {
        $this->token = isset($data->access_token) ? (string) $data->access_token : null;
    }

    /**
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @param $data
     */
    public function setIdNumber(stdClass $data): void
    {
        $this->idNumber = isset($data->user_id) ? (int) $data->user_id : null;
    }

    /**
     * @return int|null
     */
    public function getIdNumber(): ?int
    {
        return $this->idNumber;
    }

    /**
     * @param $data
     */
    public function setIsVerify($data): void
    {
        $this->isVerify = isset($data->isVerify) ? (bool) $data->isVerify : false;
    }

    /**
     * @return bool
     */
    public function isVerify(): bool
    {
        return $this->isVerify;
    }
}
