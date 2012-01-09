<?php
use Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console;

class Application_Console_Command_BuildIndex extends Application_Console_Command_Abstract
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
        ->setName('index:create')
        ->setDescription('(re)creates the whole article index')
        ->setDefinition(array(
            new InputArgument('xmlfile', true, 'A xml file containing documents'),
        ))
        ->setHelp(<<<EOT
(re)creates the whole article index
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
        
        $q = $this->_em->createQuery('DELETE FROM Application\Entity\Article');
        $q->execute();

        $config = \Zend_Registry::get('config');        
        $client = new \SolrClient($config->solr->connection->toArray());        
        $client->deleteByQuery('*:*');
        
        $this->_output->writeln('<comment>All documents deleted</comment>');
        
        if(!file_exists($input->getArgument('xmlfile'))) {
            $this->_output->writeln('<error>XML File ('.$input->getArgument('xmlfile').' should exist and be readable)</error>');
        }

        $authors = array('henk', 'jan', 'piet', 'klaas', 'ricardo', 'petra', 'sabine');
        
        $xml = simplexml_load_file($input->getArgument('xmlfile'));        
        $this->_output->writeln('<comment>Found ' . count($xml->document) . ' documents, start indexing..</comment>');
        $i = 1;        
        
        foreach($xml->document as $document) {
            $entity = new Application\Entity\Article();
            $entity->title = (string)$document->subject;
            $entity->content = (string)$document->body;
            $entity->url = (string)$document->url;
            $entity->source = (string)$document->source;
            $entity->author = $authors[array_rand($authors)];
            
            $this->_em->persist($entity);
            
            if(is_int($i / 1000)) {
                $seconds = round(microtime(true) - $startTime, 2);
                
                $this->_output->writeln('<comment>' . $i . '... (' . $seconds . ')</comment>');
                $this->_em->flush();
            }
            
            $i++;
        }
        
        $this->_em->flush();
        
        $seconds = round(microtime(true) - $startTime, 2);
        $this->_output->writeln(sprintf('<comment>Finished indexing articles in %s seconds</comment>', $seconds));
    }
}
