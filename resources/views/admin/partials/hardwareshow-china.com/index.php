<?php
error_reporting(E_ALL &~E_NOTICE &~E_STRICT);
// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));
ini_set('date.timezone','Asia/Shanghai');
require 'Star/Application.php';

// Create application, bootstrap, and run
$application = new Star_Application(
    APPLICATION_ENV,
    APPLICATION_PATH,
    APPLICATION_PATH . '/configs/config.ini',
    realpath(APPLICATION_PATH . '/../library')
);
$application->bootstrap()->setDefaultModuleName('www')->setDefaultControllerName('index')->run();
?>
