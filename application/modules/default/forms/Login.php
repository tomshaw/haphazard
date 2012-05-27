<?php
/**
 * Enter description here...
 *
 */
class Default_Form_Login extends Zend_Form
{	 

	private $elementDecorators = array(
        'ViewHelper',
        //array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'element')),
        'Label',
        array(array('row' => 'HtmlTag'), array('tag' => 'li')),
    );
 
    private $buttonDecorators = array(
        'ViewHelper',
        array(array('data' => 'HtmlTag'), array('tag' => 'span', 'class' => 'button')),
        array(array('row' => 'HtmlTag'), array('tag' => 'li')),
    );
 
    private $captchaDecorators = array(
        'Label',
        array(array('row' => 'HtmlTag'), array('tag' => 'li'))
    );
    
    private $hiddenDecorators = array(
        'ViewHelper',
        array(array('data' => 'HtmlTag'), array('tag' => 'span')),
    ); 

    public function init()
    {
    	// Sets the form name to form. 
        $this->setName('form');
        
        // Set the method for the display form to POST
        $this->setMethod('post');
    	
    	// Insert user email element.
        $this->addElement('text', 'email', array(
            'filters' => array('StringTrim', 'StringToLower'),
            'required' => true,
            'label' => 'Email Address:',
        	'decorators' => $this->elementDecorators
        ));

        // Insert user password element.
        $this->addElement('password', 'password', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                'Alnum',
                array('StringLength', false, array(6, 20)),
            ),
            'required' => true,
            'label' => 'Password:',
            'decorators' => $this->elementDecorators
        ));

        
        // Creates a submit button element.
        $this->addElement('image', 'submit', array(
            'ignore' => true,
        	'decorators' => $this->buttonDecorators,
            'label' => 'Create User',
        	'src' => '/img/submit1.gif', 
        	'class' => 'image'
        ));

        // Adds a hash CSRF protection layer.
        $this->addElement('hash', 'csrf', array(
            'ignore' => true,
        	'decorators' => $this->hiddenDecorators,
        ));

    }
    
   public function loadDefaultDecorators()
    {
        $this->setDecorators(array(
            'FormErrors',
            'FormElements',
            array('HtmlTag', array('tag' => 'ul', 'class' => 'zform')),
            'Form'
        ));
    }
}
