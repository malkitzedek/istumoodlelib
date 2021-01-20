<?php
/**
 * С помощью Небес!
 *
 * @copyright   2020 Novostruev Ivan - rusmatrix@gmail.com
 */

namespace Novostruev\istu;

use ErrorException;
use Novostruev\istu\services\{
    AuthService,
    CoursesInfoService,
    CoursesService,
    Service,
    UserService,
    UsersService
};

global $CFG;

require_once($CFG->dirroot.'/istu/classes/Config.php');
require_once($CFG->dirroot.'/istu/classes/services/AuthService.php');
require_once($CFG->dirroot.'/istu/classes/services/UserService.php');
require_once($CFG->dirroot.'/istu/classes/services/CoursesInfoService.php');
require_once($CFG->dirroot.'/istu/classes/services/CoursesService.php');
require_once($CFG->dirroot.'/istu/classes/services/UsersService.php');

require_once($CFG->dirroot.'/istu/classes/AuthRequest.php');
require_once($CFG->dirroot.'/istu/classes/CoursesInfoRequest.php');
require_once($CFG->dirroot.'/istu/classes/GeneralRequest.php');
require_once($CFG->dirroot.'/istu/classes/UserRequest.php');

/**
 * Класс предназначен для управления сервисами
 */
class ServiceManager
{
    const CONFIG_NAME = 'service_parameters';

    /**
     * @var array
     */
    private static $parameters = [];

    /**
     * @param string $name
     * @return Service
     * @throws ErrorException
     */
    public static function getService(string $name): Service
    {
        self::init();

        $params = self::getServiceParameters($name);
        switch ($name) {
            case 'auth':
                return new AuthService($params, new AuthRequest());
            case 'user':
                return new UserService($params, new UserRequest());
            case 'courses_info':
                return new CoursesInfoService($params, new CoursesInfoRequest());
            case 'users':
                return new CoursesService($params, new GeneralRequest());
            case 'courses':
                return new UsersService($params, new GeneralRequest());
            default:
                throw new ErrorException('Класс сервиса не определён');
        }
    }

    /**
     * @throws ErrorException
     */
    private static function init()
    {
        $configFile = Config::getFilename(self::CONFIG_NAME);
        if (!file_exists($configFile)) {
            throw new ErrorException('Конфигурационный файл параметров сервисов не найден');
        }

        if (empty(self::$parameters)) {
            self::$parameters = require_once($configFile);
        }
    }

    /**
     * @param string $serviceName
     * @return array
     * @throws ErrorException
     */
    private static function getServiceParameters(string $serviceName): array
    {
        if (empty(self::$parameters[$serviceName])) {
            throw new ErrorException('Соответствующие сервису параметры не определены');
        }
        return self::$parameters[$serviceName];
    }
}
