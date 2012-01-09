
<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    protected function _initAutoloaderNamespaces()
    {
        require_once APPLICATION_PATH . '/../library/Doctrine/Common/ClassLoader.php';

        $autoloader = \Zend_Loader_Autoloader::getInstance();
        $fmmAutoloader = new \Doctrine\Common\ClassLoader('Bisna');
        $autoloader->pushAutoloader(array($fmmAutoloader, 'loadClass'), 'Bisna');
    }    
    
    protected function _initConfig() {
        $config = new Zend_Config($this->getOptions(), true);
        Zend_Registry::set('config', $config);
        return $config;
    }

    /**
     * Testing if the application can be run.
     */
    protected function _initTestApplication() {
        $isWriteable = is_writable(APPLICATION_PATH . '/../library/Application/Entity/Proxy');
        if (!$isWriteable) {
            throw new Exception('The proxy directory for Doctrine should be writable by apache user.');
        }
    }
    
    protected function _initDoctrineMySQLTypes() {
        $this->bootstrap('doctrine');
        
        /* @var $doctrine Bisna\Application\Container\DoctrineContainer */
        $doctrine = $this->getResource('doctrine');
        $conn = $doctrine->getConnection();
        $conn->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
    }   

    protected function _initApplicationSession() {
        $this->bootstrap('session');
        
        Zend_Session::start();
    }   

    protected function _initEventListeners()
    {
        $this->bootstrap('doctrine');

        /* @var $doctrine \Bisna\Doctrine\Container */
        $doctrine = $this->getResource('doctrine');

        $doctrine->getEntityManager()
            ->getEventManager()
            ->addEventSubscriber(new Application\Entity\Listener\SolrDocument(\Zend_Registry::get('config')));
    }    
}

