<?php

class Application_Form_Newsletter extends Application_Form {
    public function init() {
        $this->setName('newsletterform');
        
        $element = new Zend_Form_Element_Text('subject');
        $element->setRequired(true)
                ->setLabel('Subject')
                ->addValidator(new Zend_Validate_StringLength(array('min' => '5')));
        
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Multiselect('subscriptionClasses');
        $element->setLabel('Drivers in class(es)');
        $element->setRequired(true);
        
        $subscriptionClasses = $this->_em->getRepository('Application\Entity\SubscriptionClass')
                ->findBy(array('active' => 1), array('name' => 'ASC'));

        foreach ($subscriptionClasses as $subscriptionClass) {
            $element->addMultiOption($subscriptionClass['id'], $subscriptionClass['name']);
        }        

        $this->addElement($element);
        
        $element = new Zend_Form_Element_Checkbox('generalMailingList');
        $element->setRequired(false)
                ->setLabel('Add general mailinglist');

        $this->addElement($element);        
        
        $element = new Zend_Form_Element_Textarea('body');
        $element->setRequired(true);
        $element->setAttrib('class', 'tinymce');
        $element->setLabel('Body');

        $this->addElement($element);        
        
        $element = new Zend_Form_Element_Submit('save');
        $element->setLabel('Save')
                ->setRequired(false);

        $this->addElement($element);        
    }
}