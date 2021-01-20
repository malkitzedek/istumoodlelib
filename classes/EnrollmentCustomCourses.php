<?php
/**
 * С помощью Небес!
 *
 * @copyright   2020 Novostruev Ivan - rusmatrix@gmail.com
 */

namespace Novostruev\istu;

use dml_exception;

trait EnrollmentCustomCourses
{
    /**
     * Записывает всех пользователей на курс "Техническая поддержка студентов"
     *
     * @param int $userId
     */
    private function enrollToStudentsSupportCourse(int $userId)
    {
        $this->enroll($userId, 2);
    }

    /**
     * Записывает преподавателей на курс "Техническая поддержка преподавателей"
     *
     * @param int $userId
     */
    private function enrollToTeachersSupportCourse(int $userId)
    {
        $this->enroll($userId, 3);
    }

    /**
     * Записывает преподавателей на курс "Пример курса"
     *
     * @param int $userId
     */
    private function enrollToExampleCourse(int $userId)
    {
        $this->enroll($userId, 50);
    }

    /**
     * Записывает преподавателей на курс "Защита ВКР (пример)"
     *
     * @param int $userId
     */
    private function enrollToVKRCourse(int $userId)
    {
        $this->enroll($userId, 2062);
    }

    /**
     * Записывает преподавателей на курс "Повышение квалификации "Создание электронных учебных курсов"
     *
     * @param int $userId
     */
    private function enrollToTrainingElearningCourse(int $userId)
    {
        $this->enroll($userId, 2698);
    }

    /**
     * Записывает преподавателей на курс "Защита ВКР (пример)"
     *
     * @param int $userId
     */
    private function enrollToGOCHSCourse(int $userId)
    {
        $this->enroll($userId, 5);
    }
}
