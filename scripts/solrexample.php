<?php
require_once __DIR__ . '/bootstrap.php';

// Console
$cli = new \Symfony\Component\Console\Application (
    'Solrexample command line interface',
    trim(file_get_contents(APPLICATION_PATH . '/../VERSION'))
);

try {
    // Bootstrapping Console HelperSet
    $helperSet = array();

    if (($dbal = $container->getConnection(getenv('CONN') ?: $container->defaultConnection)) !== null) {
        $helperSet['db'] = new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($dbal);
    }

    if (($em = $container->getEntityManager(getenv('EM') ?: $container->defaultEntityManager)) !== null) {
        $helperSet['em'] = new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em);
    }

    $helperSet['dialog']    = new \Symfony\Component\Console\Helper\DialogHelper();
    $helperSet['formatter'] = new \Symfony\Component\Console\Helper\FormatterHelper();
    $helperSet['zf']        = new Application_Console_Helper_ZendFramework($application, $config);
} catch (\Exception $e) {
    $cli->renderException($e, new \Symfony\Component\Console\Output\ConsoleOutput());
}

$cli->setCatchExceptions(false);
$cli->setHelperSet(new \Symfony\Component\Console\Helper\HelperSet($helperSet));

$cli->addCommands(array(
    new Application_Console_Command_BuildIndex(),
    new Application_Console_Command_OptimizeIndex()
));

try {
    $cli->run();
} catch(\Exception $e) {
//    ErrorController::logException($e, $priority = Zend_Log::ERR);
    $cli->renderException($e, new \Symfony\Component\Console\Output\ConsoleOutput());
}