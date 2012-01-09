<?php
class Application_Form_Usercp extends Application_Form {
    public function init() {
        $this->setAction($this->getView()->url(array(
            'controller' => 'usercp', 
            'action' => 'details'
        )) . '#details');
        
        $this->addSubForm(new Application_Form_SubForm_UserDetails, 'userDetails');
        
        $element = new Zend_Form_Element_Submit('save');
        $element->setLabel('Save');
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Reset('reset');
        $element->setLabel('Reset');
        $this->addElement($element);
    }
}