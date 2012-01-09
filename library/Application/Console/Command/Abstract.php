<?php
use Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console;

abstract class Application_Console_Command_Abstract extends Console\Command\Command
{
    /**
     * @var Doctrine\DBAL\Connection
     */
    protected $_connection;

    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $_em;

    /**
     * @var Symfony\Component\Console\Helper\DialogHelper
     */
    protected $_dialog;

    /**
     * @var Zend_Application
     */
    protected $_application;

    /**
     * @var Zend_Config
     */
    protected $_config;

    /**
     * @var Console\Input\Input
     */
    protected $_input;

    /**
     * @var Console\Output\Output
     */
    protected $_output;

    /**
     * @see Console\Command\Command
     */
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        $this->_connection   = $this->getHelper('db')->getConnection();
        $this->_em           = $this->getHelper('em')->getEntityManager();
        $this->_dialog       = $this->getHelper('dialog');
        $this->_application  = $this->getHelper('zf')->application;
        $this->_config       = $this->getHelper('zf')->config;
        $this->_input        = $input;
        $this->_output       = $output;
    }

    protected function dialogWriteSection(Console\Output\OutputInterface $output, $text, $style = 'bg=blue;fg=white')
    {
        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock($text, $style, true),
            '',
        ));
    }

    protected function dialogGetQuestion($question, $default, $sep = ':')
    {
        return $default ? sprintf('<info>%s</info> [<comment>%s</comment>]%s ', $question, $default, $sep) : sprintf('<info>%s</info>%s ', $question, $sep);
    }
}