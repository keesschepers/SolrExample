<?php

class Application_Form_SubscriptionClass extends Application_Form {
    public function init() {
        $this->setName('subscriptionclassform');
        
        $element = new Zend_Form_Element_Checkbox('active');
        $element->setRequired(false)
                ->setLabel('Active');

        $this->addElement($element);
        
        $element = new Zend_Form_Element_Text('name');
        $element->setRequired(true)
                ->setLabel('Name')
                ->addValidator(new Zend_Validate_StringLength(array('min' => '5')));
        
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Text('cost');
        $element->setRequired(true)
                ->setLabel('Cost')
                ->setDescription('Fill in the amount for user subscription.')
                ->addValidator(new Zend_Validate_Float());
        
        $this->addElement($element);        
        
        $element = new Zend_Form_Element_Textarea('description');
        $element->setRequired(true);
        $element->setAttrib('class', 'tinymce');
        $element->setLabel('Description');        

        $this->addElement($element);        
    }
}