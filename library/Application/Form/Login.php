<?php
class Application_Form_Login extends Application_Form {
    public function __construct($options = array()) {
        parent::__construct($options);
        
        $this->removeElement('csrf_token');
    }
    public function init() {
        $this->setAction('/usercp/login');        
        $this->setMethod('POST');
        
        $element = new Zend_Form_Element_Text('username');
        $element->setLabel('Username');        
        $element->setRequired(true);
        
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Password('password');
        $element->setLabel('Password');
        $element->setRequired(true);
        
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Submit('send');
        $element->setLabel('Login');
        $element->setRequired(false);
        $this->addElement($element);
        
    }
    
 }