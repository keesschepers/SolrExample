<?php

class Application_Form_UserSubscription extends Application_Form {
    public function init() {
        $this->setName('subscriptionform');
        
        $this->_em = Zend_Registry::get('doctrine')->getEntityManager();
        
        
        $element = new Zend_Form_Element_Select('classType');
        $element->setRequired(true)
                ->setLabel('Subscription class')
                ->addMultiOption(null, 'Choose a class..');
        
        $subscriptionClasses = $this->_em->getRepository('Application\Entity\SubscriptionClass')
                ->findBy(array('active' => 1), array('name' => 'ASC'));

        foreach ($subscriptionClasses as $subscriptionClass) {
            $element->addMultiOption($subscriptionClass['id'], $subscriptionClass['name']);
        }

        $this->addElement($element);
        
        $element = new Zend_Form_Element_Select('startNumber');
        $element->setRequired(false);
        $element->setLabel('Startnumber');
        $this->_populateStartNumbers($element);

        $this->addElement($element);        
        
        $element = new Zend_Form_Element_Text('paymentDate');
        $element->setRequired(false)
                ->setAttrib('class', 'datepicker-past')
                ->setLabel('Paymentdate')
                ->setDescription('Leave empty if the subscription hasn\'t been payed yet.')
                ->addValidator(new Zend_Validate_Date(array('format' => 'dd-mm-YYYY')));
        
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Text('requestDate');
        $element->setRequired(false)
                ->setAttrib('class', 'datepicker-past')
                ->setLabel('Requestdate')
                ->setDescription('The date when the insurrance is requested, leave empty if this subscription isn\'t requested yet.')
                ->addValidator(new Zend_Validate_Date(array('format' => 'dd-mm-YYYY')));
        
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Select('paymentMethod');
        $element->setRequired(false);
        $element->setLabel('Payment method');
        
        $config = Zend_Registry::get('config');

        $element->addMultiOption(null, 'None');
        foreach($config->paymentMethods as $paymentMethod=>$label) {
            $element->addMultiOption($paymentMethod, $label);
        }
        
        $this->addElement($element);                
    }
    
    protected function _populateStartNumbers(Zend_Form_Element_Select $element) {
        $element->addMultiOption(null, 'Choose a startnumber..');

        for ($i = 1; $i <= 250; $i++) {
            $element->addMultiOption($i, 'Startnumber: ' . $i);
        }

        return $element;
    }    
}