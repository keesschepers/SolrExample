<?php
class Application_Form_Contact extends Application_Form {
    public function init() {
        $element = new Zend_Form_Element_Text('name');
        $element->setLabel('Name');
        $element->addValidator(new Zend_Validate_StringLength(array('min' => 2, 'max' => '100')));
        $element->setRequired(true);
        
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Text('email');
        $element->setLabel('E-mailaddress');
        $element->setRequired(true);
        $element->addValidator(new Zend_Validate_EmailAddress());
        
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Select('subject');
        $element->addMultiOption('', 'Kies een onderwerp...')
            ->addMultiOption('Inschrijvingen', 'Inschrijvingen')
            ->addMultiOption('Rijdersvertegenwoordiger', 'Rijdersvertegenwoordiger')            
            ->addMultiOption('Magazine','Magazine')
            ->addMultiOption('Pitbiken', 'Pitbiken')
            ->addMultiOption('Jeugdbegeleiding', 'Jeugdbegeleiding')
            ->addMultiOption('Sprints', 'Sprints')
            ->addMultiOption('Media', 'Media')
            ->addMultiOption('Sponsoring', 'Sponsoring')
            ->addMultiOption('Verkoop', 'Verkoop')
            ->addMultiOption('Overig', 'Overig');
        
        $element->setRequired(true);
        $element->setLabel('Subject');
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Textarea('message');
        $element->setLabel('Message');
        $element->setRequired(true);
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Submit('send');
        $element->setLabel('Send');
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Reset('reset');
        $element->setLabel('Reset');
        $this->addElement($element);
    }
}