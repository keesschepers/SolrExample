<?php
class Application_Form_SubForm_UserDetails extends Application_Form_SubForm {
    public function init() {        
        
        $element = new Zend_Form_Element_Text('firstname');
        $element->setLabel('Firstname');
        $element->addValidator(new Zend_Validate_StringLength(array('min' => 2, 'max' => 25)));
        $element->setRequired(true);
        
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Text('infix');
        $element->setLabel('Infix');
        $element->addValidator(new Zend_Validate_StringLength(array('max' => 8)));
        $element->setRequired(false);
        
        $this->addElement($element);        

        $element = new Zend_Form_Element_Text('lastname');
        $element->setLabel('Lastname');
        $element->addValidator(new Zend_Validate_StringLength(array('min' => 2, 'max' => 40)));
        $element->setRequired(true);
        
        $this->addElement($element);        

        $element = new Zend_Form_Element_Text('address');
        $element->setLabel('Address');
        $element->addValidator(new Zend_Validate_StringLength(array('min' => 10, 'max' => 16)));
        $element->setRequired(true);        
        
        $this->addElement($element);         
        
        $element = new Zend_Form_Element_Text('postalCode');
        $element->setLabel('Postalcode');
        $element->addValidator(new Zend_Validate_StringLength(array('min' => 6, 'max' => 10)));
        $element->setRequired(true);        
        /**
         * @todo make sure this works for belgium and german to
         */
//        $element->addValidator(new Zend_Validate_PostCode());
        
        $this->addElement($element);                 
        
        $element = new Zend_Form_Element_Text('city');
        $element->setLabel('City');
        $element->addValidator(new Zend_Validate_StringLength(array('min' => 10, 'max' => 100)));
        $element->setRequired(true);
        
        $this->addElement($element); 
        
        $element = new Zend_Form_Element_Select('country');
        $element->setLabel('Country');        
        $element->setRequired(true);
        $element->addMultiOption('', 'Choose a country')
            ->addMultiOption('netherlands', $this->getView()->translate('Netherlands'))
            ->addMultiOption('belgium', $this->getView()->translate('Belgium'))
            ->addMultiOption('germany', $this->getView()->translate('Germany'));
        
        $this->addElement($element);         
        
        $element = new Zend_Form_Element_Text('phone');
        $element->setLabel('Phone');
        $element->addValidator(new Zend_Validate_StringLength(array('min' => 10, 'max' => 16)));
        $element->setRequired(true);        
        
        $this->addElement($element); 
        
        $element = new Zend_Form_Element_Text('email');
        $element->setLabel('E-mailaddress');
        $element->setRequired(true);
        $element->addValidator(new Zend_Validate_EmailAddress());
        
        $this->addElement($element);
    }
}