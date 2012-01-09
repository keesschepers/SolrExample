<?php
use Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console;

class Application_Console_Command_OptimizeIndex extends Application_Console_Command_Abstract
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
        ->setName('index:optimize')
        ->setDescription('Optimizes Lucene index thru solr')
        ->setHelp(<<<EOT
Optimizes the Solr/Lucene index. All segments will be merged. This process can take a while.
EOT
        );
    }

    /**
     * @see Console\Command\Command
     */
    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output)
    {
        parent::execute($input, $output);

        $startTime = microtime(true);
       
        $this->_output->writeln('<comment>Optimizing index, please wait..</comment>');
        
        $config = \Zend_Registry::get('config');        
        $client = new \SolrClient($config->solr->connection->toArray());
        $client->optimize();
        
        $seconds = round(microtime(true) - $startTime, 2);
        $this->_output->writeln(sprintf('<comment>Finished optimizing index in %s seconds</comment>', $seconds));
    }
}
