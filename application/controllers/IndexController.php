<?php

class IndexController extends Zend_Controller_Action
{

    /**
     * EntityManager
     *
     * @var Doctrine\ORM\EntityManager
     */
    protected $_em;

    public function init()
    {
        $this->_em = Zend_Registry::get('doctrine')->getEntityManager();
    }

    public function indexAction() {
        $this->view->headTitle('Choose a nice grid');
    }
    
    public function mysqlGridAction()
    {
        $this->view->headTitle('MySQL sample grid');

        /* @var $entityManager Doctrine\ORM\EntityManager */
        $entityManager = Zend_Registry::get('doctrine')->getEntityManager();
        $request = $this->getRequest();
        $query = $entityManager->createQuery('SELECT a.id, a.title, a.author, '
                . 'a.url FROM Application\Entity\Article a');

        $dataSource = new Pike_Grid_DataSource_Doctrine($query);

        $grid = new Pike_Grid($dataSource);
        $grid->setId('mysql-grid')
            ->setCaption('MySQL (slow?) grid')
            ->setRowsPerPage(20)
            ->setColumnAttribute('id', 'search', false)
            ->setColumnAttribute('title', 'search', true)
            ->setColumnAttribute('author', 'search', true)
            ->setColumnAttribute('url', 'search', true);
        
        $this->view->mysqlgrid = $grid;
        $this->view->headScript()->appendScript($grid->getJavascript(), 'text/javascript');        

        if ($request->isXmlHttpRequest()) {
            $dataSource->setParameters($request->getPost());            
            $this->_helper->json->sendJson($dataSource->getJSON(false));
        }
    }

    public function solrGridAction()
    {
        $this->view->headTitle('Solr sample grid');

        $request = $this->getRequest();
        
        $config = Zend_Registry::get('config');
        $client = new SolrClient($config->solr->connection->toArray());
        
        $query = new SolrQuery('*:*'); //all documents
        $query->addField('id')->addField('title')->addField('author')->addField('url');
        
        $dataSource = new Pike_Grid_DataSource_Solr($client, $query);

        $grid = new Pike_Grid($dataSource);
        $grid->setId('mysql-grid')
            ->setCaption('SOLR grid')
            ->setRowsPerPage(20)
            ->setColumnAttribute('id', 'search', false)
            ->setColumnAttribute('title', 'search', true)
            ->setColumnAttribute('author', 'search', true)
            ->setColumnAttribute('url', 'search', true)
            ->setColumnAttribute('url', 'sortable', false);
        
        $this->view->mysqlgrid = $grid;
        $this->view->headScript()->appendScript($grid->getJavascript(), 'text/javascript');        

        if ($request->isXmlHttpRequest()) {
            $dataSource->setParameters($request->getPost());            
            $this->_helper->json->sendJson($dataSource->getJSON(false));
        }
    }    
    
}

