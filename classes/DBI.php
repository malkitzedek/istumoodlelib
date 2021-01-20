<?php
/**
 * С помощью Небес!
 *
 * @copyright   2020 Novostruev Ivan - rusmatrix@gmail.com
 */

namespace Novostruev\istu;

use stdClass;
use dml_exception;
use moodle_database;

/**
 * Класс прездазначен для взаимодействия с БД Мудл
 *
 * Data Base Interaction
 */
class DBI
{
    private $db;

    /**
     * DBI constructor.
     * @param moodle_database $db
     */
    public function __construct(moodle_database $db)
    {
        $this->db = $db;
    }

    /**
     * @param int $userId
     * @return false|mixed|stdClass
     * @throws dml_exception
     */
    public function getUserById(int $userId)
    {
        return $this->db->get_record('user', ['id' => $userId]);
    }

    /**
     * @param int $idNumber
     * @return false|mixed
     * @throws dml_exception
     */
    public function getUserByIdNumber(int $idNumber)
    {
        return $this->db->get_record('user', ['idnumber' => $idNumber]);
    }

    /**
     * @param string $username
     * @return false|mixed|stdClass
     * @throws dml_exception
     */
    public function getUserByUsername(string $username)
    {
        return $this->db->get_record('user', ['username' => $username]);
    }

    /**
     * @param string $email
     * @return false|mixed|stdClass
     * @throws dml_exception
     */
    public function getUserByEmail(string $email)
    {
        return $this->db->get_record('user', ['email' => $email]);
    }

    /**
     * Обновляет данные в таблице 'user'
     *
     * @param int $userId
     * @param array $data
     * @param int|null $idnumber
     * @param string|null $username
     * @param string|null $password
     * @return void
     * @throws dml_exception
     */
    public function updateUserRecord(
        int $userId,
        array $data,
        int $idnumber=null,
        string $username=null,
        string $password=null
    ) {
        $dataObject                 = new stdClass();
        $dataObject->id             = $userId;
        $dataObject->firstname      = $data['firstname'];
        $dataObject->lastname       = $data['lastname'];
        $dataObject->middlename     = $data['middlename'];

        if ($data['city']) {
            $dataObject->city       = $data['city'];
        }
        if ($data['country']) {
            $dataObject->country    = $data['country'];
        }
        if ($data['email']) {
            $dataObject->email      = $data['email'];
        }
        if ($idnumber) {
            $dataObject->idnumber   = $idnumber;
        }
        if ($username) {
            $dataObject->username   = $username;
        }
        if ($password) {
            $dataObject->password   = $password;
        }

        $this->db->update_record('user', $dataObject);
    }

    /**
     * Удаляет запись текущего пользователя из таблицы 'user'
     *
     * @param int $userId Идентификатор пользователя
     * @throws dml_exception
     */
    public function deleteUserById(int $userId)
    {
        $this->db->delete_records('user', ['id' => $userId]);
    }

    /**
     * Вставляет данные (создаёт новую запись в таблице) в таблицу 'user_info_data'
     *
     * @param int $userId
     * @param int $fieldId
     * @param string $data
     * @param int $dataformat
     * @return void
     * @throws dml_exception
     */
    public function insertUserInfoDataRecord(
        int $userId,
        int $fieldId,
        string $data,
        int $dataformat=1
    ) {
        $dataObject                 = new stdClass();
        $dataObject->userid         = $userId;
        $dataObject->fieldid        = $fieldId;
        $dataObject->data           = $data;
        $dataObject->dataformat     = $dataformat;

        $this->db->insert_record('user_info_data', $dataObject, false);
    }

    /**
     * Обновляет данные в таблице 'user_info_data'
     *
     * @param int $userInfoDataId ID записи, которую требуется обновить
     * @param string $html
     * @return void
     * @throws dml_exception
     */
    public function updateUserInfoDataRecord(int $userInfoDataId, string $html)
    {
        $dataObject         = new stdClass();
        $dataObject->id     = $userInfoDataId;
        $dataObject->data   = $html;

        $this->db->update_record('user_info_data', $dataObject);
    }

    /**
     * @param int $userId
     * @param int $fieldId
     * @return false|mixed|stdClass
     * @throws dml_exception
     */
    public function getUserInfoDataRecord(int $userId, int $fieldId)
    {
        return $this->db->get_record(
            'user_info_data',
            ['userid' => $userId, 'fieldid' => $fieldId]
        );
    }

    /**
     * @param int $userId
     * @param int $enrolId
     * @return false|mixed|stdClass
     * @throws dml_exception
     */
    public function getUserEnrolmentsRecord(int $userId, int $enrolId)
    {
        return $this->db->get_record(
            'user_enrolments',
            ['enrolid' => $enrolId, 'userid' => $userId]
        );
    }

    /**
     * Вставляет данные (создаёт новую запись в таблице) в таблицу 'user_enrolments'
     *
     * @param int $userId
     * @param int $enrolId
     * @return void
     * @throws dml_exception
     */
    public function insertUserEnrolmentsRecord(int $userId, int $enrolId)
    {
        $dataObject                 = new stdClass();
        $dataObject->enrolid        = $enrolId;
        $dataObject->userid         = $userId;
        $dataObject->timestart      = time();
        $dataObject->timeend        = 0;
        $dataObject->modifierid     = 2;
        $dataObject->timecreated    = time();
        $dataObject->timemodified   = time();

        $this->db->insert_record('user_enrolments', $dataObject, false);
    }

    /**
     * @param int $courseId
     * @param string $name
     * @return false|mixed|stdClass
     * @throws dml_exception
     */
    public function getGroupRecord(int $courseId, string $name)
    {
        return $this->db->get_record(
            'groups',
            ['courseid' => $courseId, 'name' => $name]
        );
    }

    /**
     * Вставляет данные (создаёт новую запись в таблице) в таблицу 'groups'
     * и возвращает ID вновь созданной группы
     *
     * @param int $courseId
     * @param string $name
     * @return int
     * @throws dml_exception
     */
    public function insertGroupsRecord(int $courseId, string $name)
    {
        $dataObject                     = new stdClass();
        $dataObject->courseid           = $courseId;
        $dataObject->name               = $name;
        $dataObject->description        = '';
        $dataObject->descriptionformat  = 1;
        $dataObject->timecreated        = time();
        $dataObject->timemodified       = time();

        return $this->db->insert_record('groups', $dataObject, true);
    }

    /**
     * @param int $userId
     * @param int $groupId
     * @return false|mixed|stdClass
     * @throws dml_exception
     */
    public function getMemberRecord(int $userId, int $groupId)
    {
        return $this->db->get_record(
            'groups_members',
            ['groupid' => $groupId, 'userid' => $userId]
        );
    }

    /**
     * Вставляет данные (создаёт новую запись в таблице) в таблицу 'groups_members'
     *
     * @param int $userId
     * @param int $groupId
     * @return void
     * @throws dml_exception
     */
    public function insertGroupsMembersRecord(int $userId, int $groupId)
    {
        $dataObject             = new stdClass();
        $dataObject->userid     = $userId;
        $dataObject->groupid    = $groupId;
        $dataObject->timeadded  = time();

        $this->db->insert_record('groups_members', $dataObject, false);
    }

    /**
     * @param int $id
     * @return false|mixed|stdClass
     * @throws dml_exception
     */
    public function getCourseById(int $id)
    {
        return $this->db->get_record('course', ['id' => $id]);
    }

    /**
     * Получение записи из таблицы 'enrol',
     * соответствующей идентификатору курса
     *
     * @param int $courseId
     * @return stdClass|false
     * @throws dml_exception
     */
    public function getIstuEnrolByCourseId(int $courseId)
    {
        return $this->db->get_record(
            'enrol',
            ['enrol' => 'istu', 'courseid' => $courseId]
        );
    }

    /**
     * Вернётся массив объектов, иначе пустой массив
     *
     * @param int $courseId
     * @return array
     * @throws dml_exception
     */
    public function getAllEnrolByCourseId(int $courseId)
    {
        return $this->db->get_records('enrol', ['courseid' => $courseId]);
    }

    /**
     * Получение записи из таблицы 'context',
     * соответствующей идентификатору курса
     *
     * @param int $courseId
     * @return stdClass|false
     * @throws dml_exception
     */
    public function getContextByCourseId(int $courseId)
    {
        return $this->db->get_record(
            'context',
            ['contextlevel' => '50', 'instanceid' => $courseId]
        );
    }

    /**
     * @param int $userId
     * @param int $contextId
     * @return false|mixed|stdClass
     * @throws dml_exception
     */
    public function getRoleAssignments(int $userId, int $contextId)
    {
        return $this->db->get_record(
            'role_assignments',
            ['contextid' => $contextId, 'userid' => $userId]
        );
    }

    /**
     * @param int $contextId
     * @param int $userId
     * @return false|mixed|stdClass
     * @throws dml_exception
     */
    public function getTeacherRoleAssignments(int $contextId, int $userId)
    {
        return $this->db->get_record(
            'role_assignments',
            ['contextid' => $contextId, 'userid' => $userId, 'roleid' => 3]
        );
    }

    /**
     * Вставляет данные (создаёт новую запись в таблице) в таблицу 'role_assignments'
     *
     * @param int $userId
     * @param int $contextId
     * @return void
     * @throws dml_exception
     */
    public function insertRoleAssignments(int $userId, int $contextId)
    {
        $dataObject               = new stdClass();
        $dataObject->roleid       = 5;
        $dataObject->contextid    = $contextId;
        $dataObject->userid       = $userId;
        $dataObject->timemodified = time();
        $dataObject->modifierid   = 2;

        $this->db->insert_record('role_assignments', $dataObject, false);
    }
}
