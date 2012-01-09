<?php

class Application_Form_Subscription extends Application_Form {

    protected $_em;

    public function __construct($options = array()) {
        parent::__construct($options);

        $this->setExpirationTimeOnlyCsrfToken();
    }

    public function init() {
        $this->setMethod('POST');
        $this->setName('subscriptionform');

        $this->_em = Zend_Registry::get('doctrine')->getEntityManager();

        $this->addSubForm(new Application_Form_SubForm_UserDetails, 'userDetails');

        $this->_addSubscriptionClassSubForm();

        $element = new Application_Form_Element_PaymentMethod('paymentmethod');
        $element->setLabel('Payment method');
        $element->setRequired(true);

        $this->addElement($element);

        $element = new Zend_Form_Element_Submit('send');
        $element->setLabel('Send')
                ->setRequired(false);

        $this->addElement($element);
    }

    protected function _addSubscriptionClassSubForm() {
        $subForm = new Application_Form_SubForm();

        $element = new Zend_Form_Element_Radio('classType');
        $element->setRequired(true)
                ->setDisableLoadDefaultDecorators(true)
                ->setLabel('Subscription class');

        $subscriptionClasses = $this->_em->getRepository('Application\Entity\SubscriptionClass')
                ->findBy(array('active' => 1), array('name' => 'ASC'));

        foreach ($subscriptionClasses as $subscriptionClass) {
            $element->addMultiOption($subscriptionClass['id'], $subscriptionClass['name']);
        }

        $subForm->addElement($element);

        $element = new Zend_Form_Element_Select('startNumber');
        $element->setRequired(true);
        $element->setLabel('Startnumber');
        $this->_populateStartNumbers($element);

        $subForm->addElement($element);
        $subForm->setElementsBelongTo('subscriptionClass');

        $this->addSubForm($subForm, 'subscriptionClass');
    }

    protected function _populateStartNumbers(Zend_Form_Element_Select $element) {
        $element->addMultiOption(null, 'Choose a startnumber..');

        for ($i = 1; $i <= 250; $i++) {
            $element->addMultiOption($i, 'Startnumber: ' . $i);
        }

        return $element;
    }

    public function isValid($data, $namespace = null) {
        $result = parent::isValid($data, $namespace);

        if ($result) {
            $subscriptionClass = $this->_em->find('Application\Entity\SubscriptionClass', $data['subscriptionClass']['classType']);
            
            $query = $this->_em->createQueryBuilder()
                    ->select('u.id')
                    ->from('Application\Entity\UserSubscription', 'us')
                    ->join('us.user', 'u')
                    ->where('u.username = :email')
                    ->andWhere('us.subscriptionClass = :subscriptionClass')
                    ->setParameter('email', $data['userDetails']['email'])
                    ->setParameter('subscriptionClass', $subscriptionClass)
                    ->getQuery();

            $exists = count((array)$query->getArrayResult());

            if ($exists > 0) {
                $result = false;
                $flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
                $flashMessenger->setNamespace('error')
                        ->addMessage($this->getView()->translate('Your subscription has failed because you have already ' .
                                        'a account with these details with the subscription class. If you have ' .
                                        'forgotten your password please click on "forgot password" in the login bar.'));
            }
        }

        return $result;
    }

}