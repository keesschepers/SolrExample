<?php

class Application_Controller_Plugin_DetectAjax extends Zend_Controller_Plugin_Abstract {
    
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request) {
        if ($request->isXmlHttpRequest()) {
            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
            $viewRenderer->setNeverRender(true);

            Zend_Layout::getMvcInstance()->disableLayout();
        }
    }
}