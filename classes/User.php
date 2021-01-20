<?php
/**
 * С помощью Небес!
 *
 * @copyright   2020 Novostruev Ivan - rusmatrix@gmail.com
 */

/**
 * Пример содержимого аргумента конструктора
 *
 object(stdClass)#108 (6) {
   ["id"] => int(10777)
   ["fio"] => string(54) "Новоструев Иван Владимирович"
   ["fio_full"] => object(stdClass)#107 (3) {
     ["family"] => string(20) "Новоструев"
     ["name"] => string(8) "Иван"
     ["patronymic"] => string(24) "Владимирович"
   }
   ["email"] => string(27) "meaningoflife5748@gmail.com"
   ["staff"] => object(stdClass)#109 (6) {
     ["id"] => int(7777)
     ["academic_degree"] => NULL
     ["academic_title"] => NULL
     ["code"] => string(10) "0000001077"
     ["isPPS"] => bool(false)
     ["posts"] => array(1) {
       [0] => object(stdClass)#110 (3) {
         ["department"] => string(89) "Отдел информатизации образовательных процессов"
         ["parent_department"] => string(49) "Управление информатизации"
         ["post"] => string(52) "ведущий инженер-программист"
       }
     }
   }
   ["students"] => array(1) {
     [0] => object(stdClass)#114 (8) {
       ["id"] => int(10777)
       ["group"] => string(10) "А77-700-1"
       ["course"] => int(4)
       ["qualification"] => string(71) "Подготовка кадров высшей квалификации"
       ["profile"] => string(96) "Системный анализ, управление и обработка информации"
       ["speciality"] => string(69) "Информатика и вычислительная техника"
       ["education_form"] => string(10) "Очная"
       ["kafedra"] => string(62) "Кафедра «Информационные системы»"
     }
   }
 }
 */

namespace Novostruev\istu;

use stdClass;
use ErrorException;
use moodle_exception;

global $CFG;

require_once($CFG->libdir.'/moodlelib.php');
require_once($CFG->dirroot.'/istu/classes/Config.php');
require_once($CFG->dirroot.'/istu/classes/Staff.php');

class User
{
    /**
     * Это значение подставляется в поле 'idnumber' таблицы 'user'!!!
     *
     * @var int
     */
    private $id;

    /**
     * @var string|null
     */
    private $email;

    /**
     * @var string|null
     */
    private $fakePassword;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string
     */
    private $middleName;

    /**
     * @var Staff|null
     */
    public $staff;

    /**
     * @var stdClass[] Массив объектов стандартного класса
     */
    private $students = [];

    /**
     * User constructor.
     * @param stdClass $data
     * @throws moodle_exception
     * @throws ErrorException
     */
    public function __construct(stdClass $data)
    {
        $this->setId($data);
        $this->setEmail($data);
        $this->setFio($data);
        $this->setStaff($data);
        $this->setStudents($data);
        $this->setFakePassword();
    }

    /**
     * @param stdClass $data
     * @throws ErrorException
     */
    public function setId(stdClass $data)
    {
        if (!isset($data->id)) {
            throw new ErrorException('Идентификатор пользователя не определен');
        }
        $this->id = (int) $data->id;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param stdClass $data
     * @throws ErrorException
     */
    public function setEmail(stdClass $data)
    {
        if (!isset($data->email)) {
            throw new ErrorException('Email пользователя не определен');
        }

        $email = mb_strtolower((string) $data->email);
        if (!$this->filterEmail($email)) {
            throw new ErrorException('Email пользователя имеет некорректный формат');
        }
        $this->email = $email;
    }

    /**
     * @param string $email
     * @return mixed
     */
    private function filterEmail(string $email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @throws moodle_exception
     */
    private function setFakePassword()
    {
        $this->fakePassword = $this->id
            ? hash_internal_user_password((string) $this->id)
            : null;
    }

    /**
     * @return string|null
     */
    public function getFakePassword(): ?string
    {
        return $this->fakePassword;
    }

    /**
     * @param stdClass $data
     */
    public function setFio(stdClass $data)
    {
        $defaultFirstName = "User{$this->id}";
        $defaultLastName = "User";

        $fio = $data->fio_full ?? null;

        if ($fio && $fio instanceof stdClass) {
            $this->firstName = isset($fio->name)
                ? ($this->filterName(trim((string) $fio->name)) ?: $defaultFirstName)
                : $defaultFirstName;

            $this->lastName = isset($fio->family)
                ? ($this->filterName(trim((string) $fio->family)) ?: $defaultLastName)
                : $defaultLastName;


            $this->middleName = isset($fio->patronymic)
                ? ($this->filterName(trim((string) $fio->patronymic)) ?: '')
                : '';
        } else {
            $this->firstName    = $defaultFirstName;
            $this->lastName     = $defaultLastName;
            $this->middleName   = '';
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    private function filterName(string $name)
    {
        return filter_var(
            $name,
            FILTER_VALIDATE_REGEXP,
            ['options' => ['regexp' => '/^[a-zA-Zа-яА-ЯЁё\'\- ]+$/u']]
        );
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getMiddleName(): string
    {
        return $this->middleName;
    }

    /**
     * @param stdClass $data
     */
    public function setStaff(stdClass $data)
    {
        $this->staff = (isset($data->staff) && $data->staff instanceof stdClass)
            ? new Staff($data->staff)
            : null;
    }

    /**
     * @return Staff|null
     */
    public function getStaff(): ?Staff
    {
        return $this->staff;
    }

    /**
     * @param stdClass $data
     */
    public function setStudents(stdClass $data)
    {
        $this->students = (isset($data->students) && is_array($data->students))
            ? $data->students
            : [];
    }

    /**
     *
     * Пример выдачи:
     *
     array(1) {
       [0] => object(stdClass)#114 (8) {
         ["id"] => int(11620)
         ["group"] => string(10) "А17-900-1"
         ["course"] => int(4)
         ["qualification"] => string(71) "Подготовка кадров высшей квалификации"
         ["profile"] => string(96) "Системный анализ, управление и обработка информации"
         ["speciality"] => string(69) "Информатика и вычислительная техника"
         ["education_form"] => string(10) "Очная"
         ["kafedra"] => string(62) "Кафедра «Информационные системы»"
       }
     }
     *
     * @return array Массив объектов
     */
    public function getStudents(): array
    {
        return $this->students;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return '';
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return 'RU';
    }

    /**
     * @return bool
     */
    public function isTeacher(): bool
    {
        return $this->staff ? $this->staff->getIsPPS() : false;
    }

    /**
     * @return bool
     */
    public function isStudent(): bool
    {
        return !empty($this->students);
    }

    /**
     * @return bool
     */
    public function isStaff(): bool
    {
        return !empty($this->staff);
    }

    /**
     * Метод преобразует значение названия страны,
     * полученное от сервиса ИжГТУ, в двухсимвольный код страны в представлении Мудла
     *
     * @param string
     * @return string
     */
    private function getCountryCode($country)
    {
        $countryNames = require_once(Config::getFilename('country_names'));
        return $countryNames[$country] ?: 'RU';
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return [
            'firstname'    => $this->getFirstName(),
            'lastname'     => $this->getLastName(),
            'middlename'   => $this->getMiddleName(),
            'email'        => $this->getEmail(),
            'city'         => $this->getCity(),
            'country'      => $this->getCountry(),
        ];
    }

    /**
     * @param $type
     * @return array
     */
    public function getDataByType(string $type): array
    {
        switch ($type) {
            case 'staff':
                return $this->staff->getData();
            case 'student':
                return $this->students;
            default:
                return [];
        }
    }
}
