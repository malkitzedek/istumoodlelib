<?php
/**
 * С помощью Небес!
 *
 * @copyright   2020 Novostruev Ivan - rusmatrix@gmail.com
 */

/**
 * Пример содержимого аргумента конструктора
 *
  object(stdClass)#109 (6) {
    ["id"] => int(7777)
    ["academic_degree"] => NULL
    ["academic_title"] => NULL
    ["code"] => string(10) "0000007777"
    ["isPPS"] => bool(false)
    ["posts"] => array(1) {
      [0] => object(stdClass)#110 (3) {
        ["department"] => string(89) "Отдел информатизации образовательных процессов"
        ["parent_department"] => string(49) "Управление информатизации"
        ["post"] => string(52) "ведущий инженер-программист"
      }
    }
  }
 */

namespace Novostruev\istu;

use stdClass;

class Staff
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $academicDegree;

    /**
     * @var string|null
     */
    private $academicTitle;

    /**
     * @var string
     */
    private $code;

    /**
     * @var bool
     */
    private $isPPS = false;

    /**
     * @var array
     */
    private $posts = [];

    /**
     * Staff constructor.
     * @param stdClass $data
     */
    public function __construct(stdClass $data)
    {
        $this->setId($data);
        $this->setAcademicDegree($data);
        $this->setAcademicTitle($data);
        $this->setCode($data);
        $this->setIsPPS($data);
        $this->setPosts($data);
    }

    /**
     * @param stdClass $staff
     */
    public function setId(stdClass $staff)
    {
        $this->id = isset($staff->id) ? (int) $staff->id : null;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param stdClass $staff
     */
    public function setAcademicDegree(stdClass $staff)
    {
        $this->academicDegree = isset($staff->academic_degree)
            ? trim((string) $staff->academic_degree)
            : null;
    }

    /**
     * @return string|null
     */
    public function getAcademicDegree(): ?string
    {
        return $this->academicDegree;
    }

    /**
     * @param stdClass $staff
     */
    public function setAcademicTitle(stdClass $staff)
    {
        $this->academicTitle = isset($staff->academic_title)
            ? trim((string) $staff->academic_title)
            : null;
    }

    /**
     * @return string|null
     */
    public function getAcademicTitle(): ?string
    {
        return $this->academicTitle;
    }

    /**
     * @param stdClass $staff
     */
    public function setCode(stdClass $staff)
    {
        $this->code = isset($staff->code) ? trim((string) $staff->code) : '';
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param stdClass $staff
     */
    public function setIsPPS(stdClass $staff)
    {
        $this->isPPS = isset($staff->isPPS) ? (bool) $staff->isPPS : false;
    }

    /**
     * @return bool
     */
    public function getIsPPS(): bool
    {
        return $this->isPPS;
    }

    /**
     * @param stdClass $staff
     */
    public function setPosts(stdClass $staff)
    {
        $this->posts = (isset($staff->posts) && is_array($staff->posts))
            ? $staff->posts
            : [];
    }

    /**
     * @return array
     */
    public function getPosts(): array
    {
        return $this->posts;
    }

    /**
     * Пример выдачи:
     *
     array(2) {
       [0] => object(stdClass)#161 (2) {
         ["academic_degree"] => string(26) "канд. пед. наук"
         ["academic_title"] => string(12) "доцент"
       }
       [1] => object(stdClass)#111 (3) {
         ["department"] => string(89) "Отдел информатизации образовательных процессов"
         ["parent_department"] => string(49) "Управление информатизации"
         ["post"] => string(52) "ведущий инженер-программист"
       }
     }
     *
     * @return array
     */
    public function getData(): array
    {
        $academic = new stdClass();
        if ($this->getAcademicDegree()) {
            $academic->academic_degree = $this->getAcademicDegree();
        }
        if ($this->getAcademicTitle()) {
            $academic->academic_title = $this->getAcademicTitle();
        }

        $data = $this->getPosts();
        if (!empty((array) $academic)) {
            array_unshift($data, $academic);
        }
        return $data;
    }
}
