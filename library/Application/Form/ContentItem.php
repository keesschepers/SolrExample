<?php

class Application_Form_ContentItem extends Application_Form {
    public function init() {
        $this->setName('contentitemform');
        
        $element = new Zend_Form_Element_Text('title');
        $element->setRequired(true)
                ->setLabel('Title')
                ->addValidator(new Zend_Validate_StringLength(array('min' => '5')));
        
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Textarea('content');
        $element->setRequired(true);
        $element->setAttrib('class', 'tinymce');
        $element->setLabel('Content');

        $this->addElement($element);        
    }
}