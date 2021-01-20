<?php
/**
 * С помощью Небес!
 *
 * @copyright   2020 Novostruev Ivan - rusmatrix@gmail.com
 */

namespace Novostruev\istu;

use stdClass;

require_once($CFG->libdir.'/moodlelib.php');

class Course
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * Это значение подставляется в поле 'idnumber' таблицы 'user'!!!
     *
     * @var int|null
     */
    private $teacherId;

    /**
     * @var string|null
     */
    private $groupName;

    /**
     * Course constructor.
     * @param stdClass $course
     */
    public function __construct(stdClass $course)
    {
        $this->setId($course);
        $this->setTeacherId($course);
        $this->setGroupName($course);
    }

    /**
     * @param stdClass $course
     */
    public function setId(stdClass $course)
    {
        $this->id = isset($course->course_id)
            ? (int) $course->course_id
            : null;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param stdClass $course
     */
    public function setTeacherId(stdClass $course)
    {
        $this->teacherId = isset($course->teacher_id)
            ? (int) $course->teacher_id
            : null;
    }

    /**
     * @return int|null
     */
    public function getTeacherId(): ?int
    {
        return $this->teacherId;
    }

    /**
     * @param stdClass $course
     */
    public function setGroupName(stdClass $course)
    {
        $this->groupName = isset($course->group)
            ? ($this->filterGroupName(trim((string) $course->group)) ?: null)
            : null;
    }

    /**
     * @param string $groupName
     * @return mixed
     */
    private function filterGroupName(string $groupName)
    {
        $regexp = '/^[0-9a-zA-Zа-яА-ЯЁё\\()*\/_\'\- ]+$/u';
        return filter_var(
            mb_substr($groupName, 0, 100),
            FILTER_VALIDATE_REGEXP,
            ['options' => ['regexp' => $regexp]]
        );
    }

    /**
     * @return string|null
     */
    public function getGroupName(): ?string
    {
        return $this->groupName;
    }
}
