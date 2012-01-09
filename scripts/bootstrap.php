<?php
// Check if an environment is specified
$validEnvironments = array('production', 'staging', 'testing', 'development');
$commandLineArguments = isset($_SERVER['argv']) ? $_SERVER['argv'] : array();
// Check if the first argument (index 1, because index 0 is script name) is a environment
if (isset($commandLineArguments[1])) {
    if (in_array(strtolower($commandLineArguments[1]), $validEnvironments)) {
        define('APPLICATION_ENV', strtolower($commandLineArguments[1]));
        unset($commandLineArguments[1]);
        $_SERVER['argv'] = array_values($commandLineArguments);
    }
}
unset($commandLineArguments);
if (!defined('APPLICATION_ENV')) {
    echo sprintf("\033[%sm%s\033[0m", implode(';', array(37, 41)),
        'No environment specified as first argument. Possible values are "production", "staging",'
        . '"testing" or "development".') . PHP_EOL;
    exit;
}

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

$configPath = APPLICATION_PATH . '/configs/application.ini';

// Create application, bootstrap, and run
$application = new Zend_Application(APPLICATION_ENV, $configPath);

// Check if the website is in maintenance mode
$config = new Zend_Config_Ini($configPath, APPLICATION_ENV);

// Bootstrapping resources
$bootstrap = $application->bootstrap()->getBootstrap();

// Retrieve Doctrine Container resource
$container = $bootstrap->getResource('doctrine');