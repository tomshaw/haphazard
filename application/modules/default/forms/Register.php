<?php
/**
 * The user profile form. Allows users to update their system settings.
 *
 */
class Default_Form_Register extends Zend_Form
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
        // Sets the form name as form.
        $this->setName('form');

        // Set the method for the display form to POST
        $this->setMethod('post');
        
        // Used for plugin prefix paths for use *with* elements. 
        $this->addElementPrefixPath('App_Forms_Validate', 'App/Forms/Validate/', 'validate');
        
        // Used for specifying a plugin prefix path *to* elements.
        $this->addPrefixPath('App_Forms_Element', 'App/Forms/Element/', 'element'); 
        
        // Adds the user name element.
        $this->addElement('text', 'name', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                array('StringLength', false, array(0, 50)),
            ),
            'required' => true,
            'label' => 'Your full name:',
            'decorators' => $this->elementDecorators,
        ));
        
        // Adds the user email element.
        $this->addElement('text', 'email', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                array('EmailAddress', true),
                array('ValidEmail', true, array(new Default_Model_User())),
            ),
            'required' => true,
            'label' => 'Email address:',
            'decorators' => $this->elementDecorators,
        ));

        // Adds the user password element.
        $this->addElement('password', 'password', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                'Alnum',
                array('StringLength', false, array(6, 20)),
                'PasswordStrength',
            ),
            'required' => true,
            'label' => 'Password:',
            'decorators' => $this->elementDecorators,
        ));

        // Adds the verify user password element.
        $this->addElement('password', 'verify_password', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                'VerifyPassword'
            ),
            'required' => true,
            'label' => 'Verify password:',
            'decorators' => $this->elementDecorators,
        ));
        
        // Adds the user name element.
        // Adds the verify user password element.
//        $this->addElement('tinyMce', 'verifyrd', array(
//            'filters' => array('StringTrim'),
//            'validators' => array(
//                'VerifyPassword'
//            ),
//            'required' => true,
//            'label' => 'Testing:',
//            'decorators' => $this->elementDecorators,
//        ));

        // Get's a copy of the local server name.
    	$server = Zend_Controller_Front::getInstance()->getRequest()->getServer('SERVER_NAME');

    	// Adds a form captcha.
		$this->addElement(new Zend_Form_Element_Captcha('captcha', array(  
        		'captcha' => array( 
        			'captcha' => 'Image',  
	        		'wordLen' => 5,   
					'timeout' => 300,   
        			'font' => './../public/captcha/font/AARDC__.TTF',  
        			'imgDir' => './../public/captcha/',   
        			'imgUrl' => 'http://'.$server.'/captcha/'
        		),
        		'label' => 'Please type image characters:',
        		'validators' => array(
        			array('ValidCaptcha', true, array($this->getName()))
            	),
            	'class' => 'input-captcha',
            	'decorators' => $this->captchaDecorators,
        	)
        )); 

        // Creates a submit button element.
        $this->addElement('image', 'submit', array(
            'ignore' => true,
        	'decorators' => $this->buttonDecorators,
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
