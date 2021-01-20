<?php
/**
 * С помощью Небес!
 *
 * @copyright   2020 Novostruev Ivan - rusmatrix@gmail.com
 */

/**
 * Пример выгрузки:
 *
 array(1) {
   [0] => object(stdClass)#202 (3) {
     ["course_id"] => int(777)
     ["teacher_id"] => int(10)
     ["group"] => string(12) "Б18-191-2з"
   }
   [1] => object(stdClass)#202 (3) {
     ["course_id"] => int(888)
     ["teacher_id"] => int(20)
     ["group"] => string(12) "Б18-191-2з"
   }
 }
 *
 */

namespace Novostruev\istu\services;

require_once($CFG->dirroot.'/istu/classes/services/Service.php');

class CoursesInfoService extends Service{}
