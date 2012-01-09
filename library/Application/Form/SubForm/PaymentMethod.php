<?php

class Application_Form_SubForm_UserDetails extends Application_Form_SubForm {

    public function init() {
        $element = new Zend_Form_Element_Radio('paymentmethod');
        $element->setRequired(true);                
        $element->setLabel('Payment method');

        $element->addMultiOption('iDeal', 'ideal');
        $element->addMultiOption('PayPal', 'paypal');
        $element->addMultiOption('Moneyorder', 'moneyorder');

        $this->addElement($element);
        $this->setElementsBelongTo('subscriptionClass');
                
    }
    
    /*
     * render in a partial..
     */
    public function render(Zend_View_Interface $view = null) {
        return parent::render($view);
    }

}