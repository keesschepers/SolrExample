<?php
class Application_Form_Decorator_SubmitButtons
    extends Zend_Form_Decorator_Abstract
    implements Zend_Form_Decorator_Interface
{
    /**
     * Decorate content and/or element
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        return '<div class="submit-buttons">' . $content . '<div class="clear"></div></div>';
    }
}