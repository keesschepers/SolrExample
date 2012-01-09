<?php
class Application_Form_SubForm extends Zend_Form_SubForm
{
    public function __construct($options=array()) {
        parent::__construct($options);

        // Set form decorators
        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'div', 'class' => 'form-container')),
            'Fieldset'
        ));

    }

    public function addElement($element, $name = null, $options = null)
    {
        parent::addElement($element, $name, $options);

        $element->addPrefixPath('Application_Form_Decorator', 'Application/Form/Decorator', 'decorator');
        $element->setDecorators(array('Composite'));
        
        return $this;
    }
}