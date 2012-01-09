<?php

class Application_Form_Event extends Application_Form
{

    public function init()
    {
        $this->setMethod('POST');
        $this->setName('eventform');

        $element = new Zend_Form_Element_Text('name');
        $element->setLabel('Event name')
                ->addValidator(new Zend_Validate_StringLength(array('min' => 2, 'max' => 150)))
                ->addFilter(new Zend_Filter_StringTrim())
                ->setRequired(true);

        $this->addElement($element);
        
        $element = new Zend_Form_Element_Textarea('description');
        $element->setLabel('Description')
                ->setAttrib('class', 'tinymce')
                ->addValidator(new Zend_Validate_StringLength(array('min' => 40)))
                ->addFilter(new Zend_Filter_StringTrim())
                ->setRequired(false);
        
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Text('startDate');
        $element->setLabel('Start date')
                ->setAttrib('class', 'datepicker')
                ->addValidator(new Zend_Validate_Date(array('format' => 'dd-mm-YYYY')))
                ->setRequired(true);

        $this->addElement($element);
        
        $element = new Zend_Form_Element_Text('endDate');
        $element->setLabel('End date')
                ->setAttrib('class', 'datepicker')
                ->addValidator(new Zend_Validate_Date(array('format' => 'dd-mm-YYYY')))
                ->setRequired(true);

        $this->addElement($element);
        
        $element = new Zend_Form_Element_Select('eventType');
        $element->setRequired(true)
                ->setLabel('Event type')
                ->addMultiOption(null,'Please select a event type..');

        $eventTypes = $this->_em->getRepository('Application\Entity\EventType')->findAll();
        foreach ($eventTypes as $eventType) {
            $element->addMultiOption($eventType->id, $eventType->name);
        }
        
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Select('eventStatus');
        $element->setRequired(true)
                ->setLabel('Event status')
                ->addMultiOption(null, 'Please select a event status..');

        $eventStatusTypes = $this->_em->getRepository('Application\Entity\EventStatus')->findAll();
        foreach ($eventStatusTypes as $eventStatusType) {
            $element->addMultiOption($eventStatusType->id, $eventStatusType->name);
        }
        
        $this->addElement($element);

        $element = new Zend_Form_Element_Submit('send');
        $element->setLabel('Save')
                ->setRequired(false);

        $this->addElement($element);
    }

}