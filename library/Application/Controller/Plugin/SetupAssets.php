<?php

class Application_Controller_Plugin_SetupAssets extends Zend_Controller_Plugin_Abstract
{

    private $appended = false;

    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        $layout = Zend_Controller_Action_HelperBroker::getStaticHelper('layout');
        $view = $layout->getView();

        $view->headScript()->prependFile($view->baseUrl() . '/js/application.js')
                ->prependFile($view->baseUrl() . '/js/jquery.jqGrid.min.js')
                ->prependFile($view->baseUrl() . '/js/i18n/grid.locale-nl.js')
                ->prependFile($view->baseUrl() . '/js/jquery.qtip.min.js')
                ->prependFile($view->baseUrl() . '/js/jquery.cookie.js')
                ->prependFile($view->baseUrl() . '/js/jquery-ui-1.8.16.custom.min.js')
                ->prependFile($view->baseUrl() . '/js/jquery.maskedinput-1.3.min.js')
                ->prependFile($view->baseUrl() . '/js/jquery.tinymce.js')
                ->prependFile($view->baseUrl() . '/js/tiny_mce/tiny_mce.js')
                ->prependFile($view->baseUrl() . '/js/jquery-1.6.4.min.js')
                ->appendScript('Application.init();', 'text/javascript');
    }

    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $layout = Zend_Controller_Action_HelperBroker::getStaticHelper('layout');
        $view = $layout->getView();
        $path = '/css/application/' . $request->getControllerName() . '.css';

        if (file_exists(APPLICATION_PATH . '/../public' . $path)) {
            $view->headLink()->prependStylesheet($view->baseUrl() . $path);
        }

        $controller = strtolower($request->getControllerName());

        if (file_exists(APPLICATION_PATH . '/../public/js/Application/' . $controller . '.js') && !$this->appended) {

            $view->headScript()->prependFile($view->baseUrl() . '/js/Application/' . $controller . '.js');

            $inflector = new Zend_Filter_Inflector(':string');
            $inflector->addRules(array(':string' => array('Word_DashToCamelCase')));
            $controller = $inflector->filter(array('string' => $controller));


            $view->headScript()->appendScript($controller . '.init()', 'text/javascript');

            $this->appended = true;
        }
    }

}