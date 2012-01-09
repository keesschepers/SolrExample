<?php
class Application_Form_SubscribeNewsletter extends Application_Form {
    public function __construct() {
        parent::__construct();
        $this->setExpirationTimeOnlyCsrfToken('subscribenewsletter');
    }
    
    public function init() {
        
        $this->setName('subscribenewsletterform');
        
        $element = new Zend_Form_Element_Text('email');
        $element->setRequired(true)
                ->setLabel('E-mailaddress')
                ->addValidator(new Zend_Validate_EmailAddress(array('mx' => false, 'deep' => false)));
        
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Text('name');
        $element->setRequired(true)
                ->setLabel('Your name')
                ->addValidator(new Zend_Validate_StringLength(array('min' => 3, 'max' => 150)));
        
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Select('format');
        $element->setLabel('Format')
                ->addMultiOption('html', 'HTML')
                ->addMultiOption('text', 'Text');
        
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Submit('save');
        $element->setLabel('Save')
                ->setRequired(false);

        $this->addElement($element);        
    }
    
    public function isValid($data, $namespace = null)
    {
        $result = parent::isValid($data, $namespace);
        
        if($result) {
            $em = Zend_Registry::get('doctrine')->getEntityManager();
            $entity = $em->getRepository('Application\Entity\NewsletterSubscription')
                    ->findOneByEmail($this->getValue('email'));
            
            if(null !== $entity) {
                $helper = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
                $helper->setNamespace('error')->addMessage('The e-mailaddress you entered is already registered in our database.');
                
                $result = false;
            }
        }
        
        return $result;
    }
}