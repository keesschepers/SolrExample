<?php
class Application_Form extends Zend_Form
{
    /**
     * @var Zend_Translate
     */
    protected $_translate;

    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $_em;

    /**
     * Identity
     *
     * @var Application_Auth_Identity
     */
    protected $_identity;

    /**
     * Submit buttons that must be grouped together
     *
     * @var array
     */
    private $submitButtons = array();

    /**
     * Initializes the form
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        if (Zend_Registry::isRegistered('Zend_Translate')) {
            $this->_translate = Zend_Registry::get('Zend_Translate');
        }
        $this->_em = Zend_Registry::get('doctrine')->getEntityManager();
        $this->_identity = Zend_Auth::getInstance()->getIdentity();
        parent::__construct($options);
        $this->_addCsrfToken();
        $this->_setElementPrefixPathAndDecorators();
    }

    /**
     * Render form
     *
     * @param  Zend_View_Interface $view
     * @return string
     */
    public function render(Zend_View_Interface $view = null)
    {
        $this->prepareRendering();
        return parent::render($view);
    }

    /**
     * Prepares the form rendering
     */
    public function prepareRendering()
    {
        // Set form element decorators
        $this->_setElementPrefixPathAndDecorators();

        // Group submit buttons
        $this->_groupSubmitButtons();

        // Set form decorators
        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'div', 'class' => 'form-container')),
            'Form'
        ));
    }

    /**
     * Validates the form
     *
     * @param  array  $data
     * @return boolean
     */
    public function isValid($data, $namespace = null)
    {
        $isValid = parent::isValid($data);
        if (!$isValid) {
            $this->_addErrorMessagesToFlashMessenger($this);
            $this->_getMessagesFromSubForms($this->getSubForms(), $this);
            $this->_addErrorClassToInvalidElements($this);

            return false;
        } else {
            return true;
        }
    }

    /**
     * Adds submit buttons that must be grouped
     *
     * @param array $submitButtons An associative array with form element
     */
    public function addSubmitButtons(array $submitButtons)
    {
        $this->submitButtons = array_merge($this->submitButtons, $submitButtons);
    }

    /**
     * Adds a submit button that must be grouped
     *
     * @param Zend_Form_Element $submitButton
     */
    public function addSubmitButton(Zend_Form_Element $submitButton)
    {
        $submitButton->addPrefixPath('Application_Form_Decorator', 'Application/Form/Decorator', 'decorator');
        $submitButton->setDecorators(array('Composite'));
        $this->addSubmitButtons(array($submitButton));
    }

    /**
     * Clears all submit buttons for grouping
     */
    public function clearSubmitButtons()
    {
        $this->submitButtons = array();
    }

    /**
     * Sets values for the specified elements
     *
     * @param array $values An associative array with element name as key
     * @param array $prefix If specified, values are only set for fields that start
     *                      with the specified prefix
     */
    public function setElementValues(array $values, $prefix = null)
    {
        /* @var $element Zend_Form_Element */
        foreach ($this->getElements() as $element) {
            $name = str_replace($prefix, '', $element->getName());
            if (isset($values[$name])) {
                $element->setValue($values[$name]);
            }
        }
    }

    /**
     * Adds prefix path for custom decorators and registers custom decorators to the form on elements.
     */
    protected function _setElementPrefixPathAndDecorators()
    {
        foreach ($this->getElements() as $element) {
            /* @var $element Zend_Form_Element */
            $element->addPrefixPath('Application_Form_Decorator', 'Application/Form/Decorator', 'decorator');
            $element->setDecorators(array('Composite'));
        }
    }

    /**
     * Retrieves the messages from sub forms and adds them to the flash messenger
     *
     * @param array     $subForms
     * @param Zend_Form $parentForm
     */
    protected function _getMessagesFromSubForms(array $subForms, Zend_Form $parentForm)
    {
        foreach ($subForms as $subForm) {
            $this->_addErrorMessagesToFlashMessenger($subForm);
            $this->_addErrorClassToInvalidElements($subForm);

            /* @var $subForm Zend_Form_SubForm */
            if (count($subForm->getSubForms()) > 0) {
                $this->_getMessagesFromSubForms($subForm->getSubForms(), $subForm);
            }
        }
    }

    /**
     * Adds form error messages to the flash messenger
     *
     * @param Zend_Form $form
     */
    protected function _addErrorMessagesToFlashMessenger(Zend_Form $form)
    {
        $flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');

        foreach ($form->getElements() as $element) {
            if ($element instanceof Zend_Form_Element) {
                $messages = $element->getMessages();
                foreach ($messages as $message) {
                    $label = $element->getLabel();
                    $label = $label != '' ? sprintf('<strong>%s:</strong> ', $label) : null;
                    $message = $label . $message;

                    $displayGroupLegend = '';
                    $displayGroups = $form->getDisplayGroups();
                    foreach ($displayGroups as $displayGroup) {
                        foreach ($displayGroup->getElements() as $displayGroupElement) {
                            if ($displayGroupElement->name == $element->name) {
                                $displayGroupLegend = $displayGroup->getLegend();
                                break;
                            }
                        }
                    }

                    if ('' !== $displayGroupLegend) {
                        $message = sprintf('%s | ', $displayGroupLegend) . $message;
                    }

                    if (null !== $form->getLegend()) {
                        $message = sprintf('%s | ', $form->getLegend()) . $message;
                    }

                    $flashMessenger->setNamespace('error')->addMessage($message);
                }
            }
        }
    }

    /**
     * Adds an "error" CSS class to invalid form elements
     *
     * @param Zend_Form $form
     */
    protected function _addErrorClassToInvalidElements(Zend_Form $form)
    {
        foreach ($form->getElements() as $element) {
            if ($element instanceof Zend_Form_Element) {
                $errors = $element->getErrors();
                if (count($errors) > 0) {
                    $element->setAttrib('class', trim($element->getAttrib('class') . ' error'));
                    $element->removeDecorator('Errors');
                }
            }
        }
    }

    /**
     * Groups submit buttons
     */
    protected function _groupSubmitButtons()
    {
        $elements = $this->getElements();
        foreach ($elements as $element) {
            if (in_array($element->getType(), array('Zend_Form_Element_Submit', 'Zend_Form_Element_Reset'))) {
                if (!in_array($element->getName, $this->submitButtons)) {
                    $this->submitButtons[] = $element->getName();
                }
            }
        }
        if(count($this->submitButtons) > 0) {
            $this->addDisplayGroup($this->submitButtons, 'submitButtons', array(
                'decorators' => array(
                    'FormElements',
                )
            ));

            $displayGroup = $this->getDisplayGroup('submitButtons');
            $displayGroup->addPrefixPath('Application_Form_Decorator', 'Application/Form/Decorator', 'decorator');
            $displayGroup->addDecorator('SubmitButtons');
        }
    }

    /**
     * Adds a form element with the CSRF token
     */
    protected function _addCsrfToken()
    {
        $element = new Zend_Form_Element_Hash('csrf_token');
        $element->setSalt(hash('sha256', php_uname() . uniqid(rand(), true)));
        if (isset(Zend_Registry::get('config')->csrf->timeout)) {
            $element->setTimeout(Zend_Registry::get('config')->csrf->timeout);
        }
        $element->getValidator('Identical')
            ->setMessage('The form is expired or no CSRF token was provided to match against', Zend_Validate_Identical::MISSING_TOKEN);
        $this->addElement($element);
    }

    /**
     * Sets a CSRF token that causes the form to only expire base on time instead of hops also.
     *
     * Several AJAX actions in the form will otherwise cause various hops which will make the
     * CSRF token disappear and results in a token mismatch.
     *
     * @param string $namespace
     */
    public function setExpirationTimeOnlyCsrfToken($namespace = null)
    {
        $this->removeElement('csrf_token');

        $timeout = 300;
        if (isset(Zend_Registry::get('config')->Application->csrf->timeout)) {
            $timeout = Zend_Registry::get('config')->Application->csrf->timeout;
        }

        $request = Zend_Controller_Front::getInstance()->getRequest();

        if (null === $namespace) {
            $namespace = 'csrf_token_' . implode('_', array(
                $request->getModuleName(),
                $request->getControllerName(),
                $request->getActionName()
            ));
        }

        $namespace = new Zend_Session_Namespace($namespace);
        $namespace->setExpirationSeconds($timeout);

        $csrfToken = $namespace->csrfToken;
        if (null === $csrfToken) {
            $namespace->csrfToken = hash('sha256', php_uname() . uniqid(rand(), true));
        }

        $element = new Zend_Form_Element_Hidden('csrf_token');
        $element->setValue($namespace->csrfToken)
                ->setRequired(true)
                ->addValidator('Identical', true, array($csrfToken))
                ->getValidator('Identical')->setMessage('The form is expired or no CSRF token was'
                    . ' provided to match against', Zend_Validate_Identical::MISSING_TOKEN);
        $this->addElement($element);
    }
}
