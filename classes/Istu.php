<?php
/**
 * С помощью Небес!
 *
 * @copyright   2020 Novostruev Ivan - rusmatrix@gmail.com
 */

namespace Novostruev\istu;

use stdClass;
use Exception;
use ErrorException;
use ReflectionObject;
use dml_exception;
use moodle_database;
use moodle_exception;
use Novostruev\istu\services\Service;

// подключение главной библиотеки Мудла
require_once($CFG->libdir.'/moodlelib.php');

require_once($CFG->dirroot.'/istu/classes/EnrollmentCustomCourses.php');

class Istu
{
    use EnrollmentCustomCourses;

    /**
     * @var DBI
     */
    private $db;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Auth
     */
    public $auth = null;

    /**
     * Istu constructor.
     *
     * @param moodle_database $db
     */
    public function __construct(moodle_database $db)
    {
        global $CFG;

        $this->loadClasses($CFG->dirroot);

        $this->db = new DBI($db);
        $this->logger = new Logger($CFG->dataroot);
    }

    /**
     * @param string $dirRoot
     */
    private function loadClasses(string $dirRoot)
    {
        $requiredClasses = [
            'DBI',
            'Auth',
            'User',
            'Course',
            'Logger',
            'Profile',
            'UserInfo',
            'ServiceManager',
        ];

        foreach ($requiredClasses as $className) {
            require_once("{$dirRoot}/istu/classes/{$className}.php");
        }
    }

    /**
     * @param string $text
     * @param string|null $filename
     */
    public function log(string $text, string $filename=null)
    {
        $this->logger->log($text, $filename);
    }

    /**
     * @param string $serviceName
     * @return Service
     * @throws ErrorException
     */
    public function getService(string $serviceName): Service
    {
        return ServiceManager::getService($serviceName);
    }

    /**
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function getAuth(string $username, string $password): bool
    {
        try {
            $params = ['email' => $username, 'password' => $password];
            $data = $this->getService('auth')->getData($params);
            $data->username = $username;
            $data->password = $password;
            $this->auth = new Auth($data);
        } catch (ErrorException $e) {
            return false;
        }
        return $this->auth->getToken() && $this->auth->getIdNumber();
    }

    /**
     * @return bool
     */
    public function isUserVerified(): bool
    {
        return isset($this->auth) ? $this->auth->isVerify() : false;
    }

    /**
     * @return string|null
     */
    public function getAuthToken(): ?string
    {
        return isset($this->auth) ? $this->auth->getToken() : null;
    }

    /**
     * @param stdClass $moodleUser
     */
    public function performActionsWhenLoggedIn(stdClass $moodleUser)
    {
        try {
            $istuUser = $this->getIstuUser(['token' => $this->getAuthToken()]);
            $this->updateUserWhenLogin($moodleUser, $istuUser);
            $this->updateProfileFieldsWhenLogin($moodleUser->id, $istuUser);
            $this->enrollUserWhenLogin($moodleUser->id, $istuUser);
        } catch (Exception $e) {
            $this->log($e->getMessage());
            return;
        }
    }

    /**
     * @param array $params
     * @return User
     * @throws ErrorException
     * @throws moodle_exception
     */
    private function getIstuUser(array $params): User
    {
        return new User($this->getService('user')->getData($params));
    }

    /**
     * @param string $fieldName
     * @param $fieldValue
     * @return false|mixed|stdClass
     * @throws dml_exception
     */
    public function getMoodleUser(string $fieldName, $fieldValue)
    {
        switch ($fieldName) {
            case 'id':
                return $this->db->getUserById($fieldValue);
            case 'idnumber':
                return $this->db->getUserByIdNumber($fieldValue);
            case 'username':
                return $this->db->getUserByUsername($fieldValue);
            case 'email':
                return $this->db->getUserByEmail($fieldValue);
            default:
                return null;
        }
    }

    /**
     * Обновляет пользователя Мудл
     *
     * @param stdClass $user Объект пользователя
     * @param User $istuUser
     * @return void
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function updateUserWhenLogin(stdClass $user, User $istuUser)
    {
        $istuUserData = $istuUser->getData();
        if ($userRecord = $this->db->getUserByIdNumber($istuUser->getId())) {
            // Случай, когда персональный идентификатор пользователя, $idNumber, СУЩЕСТВУЕТ в БД
            $userId = $userRecord->id;

            if ($userId === $user->id) {
                // Случай, когда пользователь повторно входит в Систему с прежним логином
                $istuUserData['email'] = false;
                $this->db->updateUserRecord($userId, $istuUserData);
            } else {
                // Случай, когда пользователь входит в систему под новым логином
                $this->ensureUniqueUser($user, $userId, $user->id, $istuUserData);
            }
        } else {
            // Случай, когда в БД ещё нет персонального идентификатора пользователя,
            // то есть пользователь вошёл в Систему В ПЕРВЫЙ РАЗ
            //
            // Обновление соответствующей записи из таблицы 'user'
            // Эта запись была создана автоматически самим Мудлом
            // сразу же после успешной аутентификации!
            $this->db->updateUserRecord($user->id, $istuUserData, $istuUser->getId());

            // Обновление объекта $user
            // Это нужно, чтобы после первого входа в Систему
            // пользователя не перенаправляло на страницу редактирования профиля
            $this->overrideUserObjectProperties($user, $this->db->getUserById($user->id));
        }
    }

    /**
     * Обеспечивает уникальность пользователей в БД Мудл
     * Обеспечение уникальности пользователей осуществляется за счёт
     * проверки их персональных идентификаторов,
     * получаемых от сервиса аутентификации ИжГТУ
     *
     * @param stdClass $user
     * @param int $oldUserId
     * @param int $newUserId
     * @param array $data
     * @return void
     * @throws moodle_exception
     */
    private function ensureUniqueUser(
        stdClass $user,
        int $oldUserId,
        int $newUserId,
        array $data
    ) {
        $data['email'] = false;
        $this->db->deleteUserById($newUserId);

        $this->db->updateUserRecord(
            $oldUserId,
            $data,
            false,
            $this->auth->getUsername(),
            hash_internal_user_password($this->auth->getPassword(), false)
        );

        $this->overrideUserObjectProperties($user, $this->db->getUserById($oldUserId));
    }

    /**
     * Переопределяет значения свойств объекта $user
     * соответствующими значениями свойств объекта $updatedUser
     *
     * @param stdClass $user
     * @param stdClass $newUser Обновленная запись пользователя в БД
     */
    private function overrideUserObjectProperties(stdClass $user, stdClass $newUser) {
        $reflectionUser = new ReflectionObject($user);
        $properties = (new ReflectionObject($newUser))->getProperties();
        foreach ($properties as $property) {
            $name = $property->getName();
            if ($reflectionUser->hasProperty($name)) {
                $user->$name = $newUser->$name;
            }
        }
    }

    /**
     * @param int $moodleUserId
     * @param User $istuUser
     * @throws dml_exception
     * @throws ErrorException
     */
    private function updateProfileFieldsWhenLogin(int $moodleUserId, User $istuUser)
    {
        $this->updateProfileFields($moodleUserId, $istuUser, new Profile($this->db));
    }

    /**
     * Обновляет профиль пользователя данными,
     * полученными от сервиса пользователей
     *
     * @param int $moodleUserId
     * @param User $istuUser
     * @param Profile $profile
     * @throws ErrorException
     * @throws dml_exception
     */
    private function updateProfileFields(int $moodleUserId, User $istuUser, Profile $profile)
    {
        if ($istuUser->isStaff()) {
            $this->fillInfoPersonField($moodleUserId, $istuUser, $profile);
            $this->fillEmployeeCodeField($moodleUserId, $istuUser, $profile);
        }

        if ($istuUser->isStudent()) {
            $this->fillInfoStudentField($moodleUserId, $istuUser, $profile);
        }
    }

    /**
     * @param int $userId
     * @param User $istuUser
     * @param Profile $profile
     * @throws ErrorException
     * @throws dml_exception
     */
    private function fillInfoPersonField(int $userId, User $istuUser, Profile $profile)
    {
        $profile::fillField(
            'infoperson',
            $userId,
            UserInfo::get('staff', $istuUser->getDataByType('staff'))
        );
    }

    /**
     * @param int $userId
     * @param User $istuUser
     * @param Profile $profile
     * @throws dml_exception
     */
    private function fillEmployeeCodeField(int $userId, User $istuUser, Profile $profile)
    {
        $profile::fillField(
            'employeecode',
            $userId,
            $istuUser->staff->getCode()
        );
    }

    /**
     * @param int $userId
     * @param User $istuUser
     * @param Profile $profile
     * @throws ErrorException
     * @throws dml_exception
     */
    private function fillInfoStudentField(int $userId, User $istuUser, Profile $profile)
    {
        $profile::fillField(
            'infostudent',
            $userId,
            UserInfo::get('student', $istuUser->getDataByType('student'))
        );
    }

    /**
     * Осуществляет запись пользователя на курсы
     *
     * @param int $moodleUserId
     * @param User $istuUser
     * @throws dml_exception
     * @throws ErrorException
     */
    public function enrollUserWhenLogin(int $moodleUserId, User $istuUser)
    {
        if ($istuUser->isStudent()) {
            $this->enrollToStudentsSupportCourse($moodleUserId);
        }

        if ($istuUser->isTeacher()) {
            $this->enrollToTeachersSupportCourse($moodleUserId);
            $this->enrollToVKRCourse($moodleUserId);
            $this->enrollToExampleCourse($moodleUserId);
            $this->enrollToTrainingElearningCourse($moodleUserId);
        }

        if ($istuUser->isStaff()) {
            $this->enrollToGOCHSCourse($moodleUserId);
        }

        $this->enrollIstuCourses($moodleUserId);
    }

    /**
     * @param int $moodleUserId
     * @throws dml_exception
     * @throws ErrorException
     */
    private function enrollIstuCourses(int $moodleUserId)
    {
        $params = ['token' => $this->getAuthToken()];
        $courses = $this->getService('courses_info')->getData($params);
        foreach ($courses as $course) {
            if ($course instanceof stdClass) {
                $this->enrollmentInCourse($moodleUserId, new Course($course));
            }
        }
    }

    /**
     * @param int $userId
     * @param Course $course
     * @throws dml_exception
     */
    private function enrollmentInCourse(int $userId, Course $course)
    {
        if (
            !($courseId = $course->getId())
            || !($teacherIdNumber = $course->getTeacherId())
            || !$this->db->getCourseById($courseId)
            || !$this->teacherExistsOnCourse($courseId, $teacherIdNumber)
        ) {
            return;
        }

        $this->enroll($userId, $courseId);

        if ($groupName = $course->getGroupName()) {
            $this->addToLocalGroup($userId, $courseId, $groupName);
        }
    }

    /**
     * @param int $courseId
     * @param int $teacherIdNumber
     * @return bool
     * @throws dml_exception
     */
    private function teacherExistsOnCourse(int $courseId, int $teacherIdNumber): bool
    {
        return ($context = $this->db->getContextByCourseId($courseId))
            && ($teacher = $this->db->getUserByIdNumber($teacherIdNumber))
            && $this->db->getTeacherRoleAssignments($context->id, $teacher->id);
    }

    /**
     * Записывает пользователя на определенный курс с ролью "Студент"
     *
     * Если $enrol = false, то $enrol->id = NULL,
     * то при попытке входа пользователя в Систему
     * возникнет ошибка: "Ошибка записи в базу данных".
     * Данная ошибка возникает из-за того, что поле 'enrolid' таблицы 'user_enrolments',
     * в которое метод insertIntoUserEnrolments()
     * пытается записать значение переменной $enrol->id,
     * по условию может принимать ТОЛЬКО значения типа данных bigint(10),
     * и не может принимать значения типа данных NULL!!!
     *
     * @param int $userId
     * @param int $courseId
     * @throws dml_exception
     */
    private function enroll(int $userId, int $courseId)
    {
        // Если способов записи для курса не найдено,
        // или
        // Если в курсе не установлен способ записи "through ISTU"
        // или
        // Если пользователь уже записан на курс с помощью любого из способов записи
        //
        // то прекратить выполнении функции
        if (
            empty($enrols = $this->db->getAllEnrolByCourseId($courseId))
            || !($istuEnrol = $this->getIstuEnrol($enrols))
            || $this->isUserEnrolled($userId, $enrols)
        ) {
            return;
        }

        $this->db->insertUserEnrolmentsRecord($userId, $istuEnrol->id);
        $this->assignRole($userId, $courseId);
    }

    /**
     * @param array $enrols
     * @return stdClass|null
     */
    function getIstuEnrol(array $enrols): ?stdClass
    {
        foreach ($enrols as $enrol) {
            if ($enrol->enrol === 'istu') {
                return $enrol;
            }
        }
        return null;
    }

    /**
     * Проверяет, записан ли пользователь на курс
     *
     * @param array $enrols
     * @param int $userId
     * @return bool
     * @throws dml_exception
     */
    function isUserEnrolled(int $userId, array $enrols): bool
    {
        foreach ($enrols as $enrol) {
            if ($this->db->getUserEnrolmentsRecord($userId, $enrol->id)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Назначает пользователю роль на определенном курсе
     *
     * @param int $userId
     * @param int $courseId
     * @throws dml_exception
     */
    private function assignRole(int $userId, int $courseId)
    {
        // Если переменной $context->id НЕ СУЩЕСТВУЕТ,
        // или пользователю уже назначена роль на курсе,
        // то выполнение функции прекращается
        if (
            !($context = $this->db->getContextByCourseId($courseId))
            || $this->db->getRoleAssignments($userId, $context->id)
        ) {
            return;
        }
        $this->db->insertRoleAssignments($userId, $context->id);
    }

    /**
     * Создаёт группу внутри курса (т.н. локальную групу) и записывает в неё пользователя
     *
     * @param int $userId
     * @param int $courseId
     * @param string $groupName
     * @return void
     * @throws dml_exception
     */
    private function addToLocalGroup(int $userId, int $courseId, string $groupName)
    {
        if (!$group = $this->db->getGroupRecord($courseId, $groupName)) {
            // Создаём локальную группу и получаем её ID
            $groupId = $this->db->insertGroupsRecord($courseId, $groupName);

            // Записываем пользователя в группу
            $this->db->insertGroupsMembersRecord($userId, $groupId);

            // Проверка, записан ли пользователь в группу,
            // и если НЕТ, то записываем его
        } else if (!$this->db->getMemberRecord($userId, $group->id)) {
            // Записываем пользователя в группу
            $this->db->insertGroupsMembersRecord($userId, $group->id);
        }
    }

    /**
     * @param array $objects
     * @throws moodle_exception
     * @throws ErrorException
     */
    public function importUsers(array $objects)
    {
        foreach ($objects as $object) {
            if (!$object instanceof stdClass) {
                continue;
            }

            try {
                $istuUser = new User($object);
            } catch (ErrorException $e) {
                $this->log($e->getMessage());
                continue;
            }

            // Если пользователь уже существует в БД Мудл,
            // или
            // Если пользователь не является сотрудником или студентом,
            // значит он не верифицирован
            //
            // то переходим к следующей итерации
            if (
                !($id = $istuUser->getId())
                || !($username = $email = $istuUser->getEmail())
                || !($password = $istuUser->getFakePassword())
                || $this->db->getUserByIdNumber($id)
                || $this->db->getUserByUsername($username)
                || $this->db->getUserByEmail($email)
                || (!$istuUser->isStaff() && !$istuUser->isStudent())
            ) {
                continue;
            }

            try {
                // создание нового пользователя
                if ($user = create_user_record($username, $password, 'istu')) {
                    $this->updateUser($user, $istuUser);
                    $this->updateProfileFields($user->id, $istuUser, new Profile($this->db));
                }
            } catch (moodle_exception $e) {
                $this->log("{$e->getMessage()}-{$istuUser->getId()}-{$username}");
                continue;
            }
            unset($istuUser);
        }
    }

    /**
     * Случай, когда в БД ещё нет персонального идентификатора пользователя
     *
     * Обновление соответствующей записи из таблицы 'user'
     *
     * @param stdClass $user
     * @param User $istuUser
     * @throws dml_exception
     */
    private function updateUser(stdClass $user, User $istuUser)
    {
        $this->db->updateUserRecord($user->id, $istuUser->getData(), $istuUser->getId());
    }

    /**
     * @param array $objects
     * @throws dml_exception
     *
    array(850) {
        [0] => object(stdClass)#85 (4) {
            ["course_id"] => string(4) "1655"
            ["teacher_id"] => int(1436)
            ["group"] => string(10) "Б02-500-7"
            ["students"] => array(2) {
                [0] => int(26335)
                [1] => int(26363)
            }
        }
    }
     */
    public function enrollmentInCourses(array $objects)
    {
        foreach ($objects as $object) {
            if (
                !($object instanceof stdClass)
                || empty($studentIds = $this->getStudentIdsFromObject($object))
            ) {
                continue;
            }

            $course = new Course($object);
            foreach ($studentIds as $studentId) {
                if (
                    isset($studentId)
                    && ($user = $this->db->getUserByIdNumber((int) $studentId))
                ) {
                    $this->enrollmentInCourse($user->id, $course);
                }
            }
        }
    }

    /**
     * @param stdClass $object
     * @return array
     */
    private function getStudentIdsFromObject(stdClass $object): array
    {
        return (isset($object->students) && is_array($object->students))
            ? $object->students
            : [];
    }

    /**
     * @param stdClass $object
     * @return int|null
     */
    private function getIdNumberFromObject(stdClass $object): ?int
    {
        return isset($object->user_id) ? (int) $object->user_id : null;
    }

    /**
     * @param stdClass $object
     * @return array
     */
    private function getCoursesFromObject(stdClass $object): array
    {
        return (isset($object->courses) && is_array($object->courses))
            ? $object->courses
            : [];
    }

    /**
     * @param array $objects
     * @throws dml_exception
     *
    [
        [0] => object(stdClass)#5735 (2) {
            ["user_id"] => int(400)
            ["courses"] => array(3) {
                [0] => object(stdClass)#73 (3) {
                    ["course_id"] => int(370)
                    ["teacher_id"] => int(12)
                    ["group"] => string(18) "А08-0900(2016-01)"
                }
                [1] => object(stdClass)#133 (3) {
                    ["course_id"] => int(390)
                    ["teacher_id"] => int(12)
                    ["group"] => string(18) "А08-0900(2016-01)"
                }
                [2] => object(stdClass)#74 (3) {
                    ["course_id"] => int(538)
                    ["teacher_id"] => int(12)
                    ["group"] => string(18) "А08-0900(2016-01)"
                }
            }
        },
        [1] => object(stdClass)#5735 (2) {
            ["user_id"] => int(700)
            ["courses"] => array(3) {
                [0] => object(stdClass)#73 (3) {
                    ["course_id"] => int(370)
                    ["teacher_id"] => int(12)
                    ["group"] => string(18) "А08-0900(2016-01)"
                }
                [1] => object(stdClass)#133 (3) {
                    ["course_id"] => int(390)
                    ["teacher_id"] => int(12)
                    ["group"] => string(18) "А08-0900(2016-01)"
                }
            }
        },
    ]
     */
    public function oldEnrollmentInCourses(array $objects)
    {
        foreach ($objects as $object) {
            if (
                !($object instanceof stdClass)
                || !($idNumber = $this->getIdNumberFromObject($object))
                || empty($courses = $this->getCoursesFromObject($object))
                || !($user = $this->db->getUserByIdNumber($idNumber))
            ) {
                continue;
            }

            foreach ($courses as $course) {
                if ($course instanceof stdClass) {
                    $this->enrollmentInCourse($user->id, new Course($course));
                }
            }
        }
    }
}
