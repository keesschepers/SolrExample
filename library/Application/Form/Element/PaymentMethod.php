<?php

class Application_Form_Element_PaymentMethod extends Zend_Form_Element_Radio {

    public function __construct($spec, $options = null) {
        parent::__construct($spec, $options);
        
        $config = Zend_Registry::get('config');
        foreach($config->paymentMethods as $name=>$label) {
            $this->addMultiOption($name, $label);
        }               
    }
    
    public function render(Zend_View_Interface $view = null) {
        if ($this->_isPartialRendering) {
            return '';
        }

        if (null !== $view) {
            $this->setView($view);
        }
        
        $view = $this->getView();
        
        return $view->partial('partials/payment-method.phtml', array('element' => $this));
    }

}