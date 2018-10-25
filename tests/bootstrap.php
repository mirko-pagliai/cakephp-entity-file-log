<?php
/**
 * This file is part of cakephp-entity-file-log.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Mirko Pagliai
 * @link        https://github.com/mirko-pagliai/cakephp-entity-file-log
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
use Cake\Core\Configure;
use Cake\Log\Log;
use EntityFileLog\Log\Engine\EntityFileLog;

ini_set('intl.default_locale', 'en_US');

require dirname(__DIR__) . '/vendor/autoload.php';

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

// Path constants to a few helpful things.
define('ROOT', dirname(__DIR__) . DS);
define('CORE_PATH', ROOT . 'vendor' . DS . 'cakephp' . DS . 'cakephp' . DS);
define('CAKE', CORE_PATH . 'src' . DS);
define('TESTS', ROOT . 'tests' . DS);
define('TMP', sys_get_temp_dir() . DS . 'cakephp-entity-log' . DS);
define('LOGS', TMP . 'logs' . DS);

safe_mkdir(TMP);
safe_mkdir(LOGS);

require_once CORE_PATH . 'config' . DS . 'bootstrap.php';

if (!function_exists('loadPHPUnitAliases')) {
    function loadPHPUnitAliases()
    {
        require_once dirname(__DIR__) . DS . 'tests' . DS . 'phpunit_aliases.php';
    }
}
loadPHPUnitAliases();

if (version_compare(Configure::version(), '3.6', '>=')) {
    error_reporting(E_ALL & ~E_USER_DEPRECATED);
}

date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

Log::config('error', [
    'className' => EntityFileLog::class,
    'path' => LOGS,
    'file' => 'error',
    'levels' => ['warning', 'error', 'critical', 'alert', 'emergency'],
]);

$_SERVER['PHP_SELF'] = '/';
