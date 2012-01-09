<?php
use Symfony\Component\Console\Helper\Helper;

class Application_Console_Helper_ZendFramework extends Helper
{
    /**
     * @var Zend_Application
     */
    public $application;

    /**
     * @var Zend_Config
     */
    public $config;

    /**
     * Constructor
     *
     * @param Zend_Application $application
     * @param Zend_Config $config
     */
    public function __construct(Zend_Application $application, Zend_Config $config)
    {
        $this->application = $application;
        $this->config = $config;
    }

    /**
     * Returns the helper's canonical name
     *
     * @return string The canonical name of the helper
     */
    public function getName()
    {
        return 'zf';
    }
}
