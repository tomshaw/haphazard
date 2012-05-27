<?php
/**
 * The main user administration form for creating and editing users.
 *
 */
class Admin_Form_UserCreate extends Zend_Form
{
	/**
	 * 
	 */
	private $elementDecorators = array(
        'ViewHelper',
        //array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'element')),
        'Label',
        array(array('row' => 'HtmlTag'), array('tag' => 'li')),
    );
 
    /**
     * 
     */
    private $buttonDecorators = array(
        'ViewHelper',
        array(array('data' => 'HtmlTag'), array('tag' => 'fieldset', 'class' => 'submit-buttons')),
        //array(array('row' => 'HtmlTag'), array('tag' => 'li')),
    );
 
    /**
     * 
     */
    private $captchaDecorators = array(
        'Label',
        array(array('row' => 'HtmlTag'), array('tag' => 'li'))
    );
    
    /**
     * 
     */
    private $hiddenDecorators = array(
        'ViewHelper',
        array(array('data' => 'HtmlTag'), array('tag' => 'span')),
    );
	/**
	 * Initiates the main form.
	 *
	 */
    public function init()
    {
        // Sets the form name as form.
        $this->setName('form');

        // Set the method for the display form to POST
        $this->setMethod('post');

        // Adds path to check for validators.
        $this->addElementPrefixPath('App_Forms_Validate', 'App/Forms/Validate/', 'validate');
        
        // Adds a user id element. When creating a new row we remove the element from the controller.
        $this->addElement('hidden', 'id', array(
            'filters' => array('StringTrim'),
            'validators' => array('Int'),
            'required' => true,
        	'decorators' => $this->hiddenDecorators,
        ));
        
        // Creates a user name element.
        $this->addElement('text', 'name', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                array('StringLength', false, array(0, 50)),
            ),
            'required' => true,
            'label' => 'User name',
            //'decorators' => $this->elementDecorators,
            'class' => 'input-medium',
        ));
        
        // Creates a user email element.
        $this->addElement('text', 'email', array(
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('EmailAddress', true),
                array('ValidEmail', true, array(new Default_Model_User())),
            ),
            'required' => true,
            'label' => 'Email address',
            //'decorators' => $this->elementDecorators,
            'class' => 'input-medium',
        ));

        // Creates a user password element.
        $this->addElement('password', 'password', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                'Alnum',
                array('StringLength', false, array(6, 20)),
                'PasswordStrength',
            ),
            'required' => true,
            'label' => 'Password',
            //'decorators' => $this->elementDecorators,
            'class' => 'input-medium',
        ));
        
        // Creates a verify password element.
        $this->addElement('password', 'verify_password', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                'VerifyPassword'
            ),
            'required' => true,
            'label' => 'Verify password',
            //'decorators' => $this->elementDecorators,
            'class' => 'input-medium',
        ));

        // Creates a user identity selection element.
        $this->addElement('select', 'identity', array(
        	'label' => 'User identity',
            'required' => true,
            'multiOptions' => array(
        		0 => 'Anonymous', 
				1 => 'Member',
				2 => 'Author',
				3 => 'Moderator',
				4 => 'Administrator'),
        	'value' => 1,
			//'decorators' => $this->elementDecorators,
			'class' => 'input-medium',
        ));

        // Creates a user active select element.
        $this->addElement('select', 'active', array(
            'required' => true,
            'multiOptions' => array(1 => 'Yes', 0 => 'No'),
            'label' => 'User active',
        	'value' => 1,
        	//'decorators' => $this->elementDecorators,
        ));
        
        // Adds radio buttons hidden user.
        $this->addElement('checkbox', 'hidden', array(
            'required' => false,
            'multiOptions' => array(0 => 'No', 1 => 'Yes'),
            'label' => 'User hidden',
        	'value' => 0,
        	//'decorators' => $this->elementDecorators,
        	//'class' => 'input-checkbox'
        ));
			
        // Creates a user comment area where users can type 
        // some useful information about themselves. The validator 
        // allows comments up to 1250 characters. 
        $this->addElement('textarea', 'comment', array(
            'label' => 'Comment',
            'required' => false,
            'validators' => array(
                array(
                	'validator' => 'StringLength', 
                	'options' => array(0, 1250)
                )
            ),
            'attribs' => array(
            	'cols' => 45,
            	'rows' => 10
            ),
            //'decorators' => $this->elementDecorators,
            'class' => 'input-medium',
        ));
        
        // Creates a submit button element.
        $this->addElement('submit', 'submit', array(
            'ignore' => true,
        	'decorators' => $this->buttonDecorators,
            'label' => 'Create User',
        	//'src' => '/img/btn_submit.gif', 
        	'class' => 'button2'
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
            array('HtmlTag', array('tag' => 'fieldset', 'class' => 'fields2')),
            'Form'
        ));
    }
}
?>