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

    public function indexAction()
    {
        $this->view->headTitle('Some grids');

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

}

