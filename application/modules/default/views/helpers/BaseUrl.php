<?php
/**
 * HeaderLinks helper
 *
 */
class App_View_Helper_BaseUrl
{
    public $view;

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function baseUrl()
    {
        return 'haphazard.dev';
    }

}