<?php
/**
 * The user profile form. Allows users to update their system settings.
 *
 */
class Default_Form_Setup extends Zend_Form
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
    
    private $fileDecorators = array(
        'ViewHelper',
        array(array('row' => 'file'), array('tag' => 'li'))
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
        
        // Make sure were prepared for file uploads.
        $this->setAttrib('enctype', Zend_Form::ENCTYPE_MULTIPART);
        
        // Adds paths for custom form validation.
        $this->addElementPrefixPath('App_Forms_Validate', 'App/Forms/Validate/', 'validate');
        
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
                //array('ValidEmail', true, array(new Default_Model_Users())),
            ),
            'required' => false,
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
            'required' => false,
            'label' => 'Password:',
            'decorators' => $this->elementDecorators,
        ));

        // Adds the verify user password element.
        $this->addElement('password', 'verify_password', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                'VerifyPassword'
            ),
            'required' => false,
            'label' => 'Verify password:',
            'decorators' => $this->elementDecorators,
        ));
        
        // Adds radio buttons hidden user.
        $this->addElement('checkbox', 'hidden', array(
            'required' => false,
        	'decorators' => $this->elementDecorators,
            'multiOptions' => array(0 => 'No', 1 => 'Yes'),
            'label' => 'Hide your profile?',
        	'value' => 0,
        	'class' => 'input-checkbox'
        ));
        
        // Create the email input element.
//        $this->addElement('file', 'file', array(
//            'label' => 'Upload Avatar:',
//            'required' => false,
//            'filters' => array('StringTrim'),
//     		'validators' => array(
//        		array('Count', true, 2),
//        		array('Size', true, '100kb'),
//        		//array('FilesSize', array('1B', '100KB'),
//        		array('ImageSize', true, array(0,0,300,300)), // min height/width max height/width
//        		array('Extension', true, 'jpg'),
//        		//array('Extension', false, 'avi,mpg,mpeg,flv,png'),
//        		//array('MimeType', true, array('image/gif','image/jpeg','image/png')),
//        		//array('MimeType', true, 'image'),
//            ),
//        	'destination' => 'uploads',
//            //'decorators' => $this->fileDecorators,
//        	//'multifile' => 3 Adds three file upload inputs
//        ));

        // Creates a user comment area where users can type 
        // some useful information about themselves. The validator 
        // allows comments up to 1250 characters. 
        $this->addElement('textarea', 'comment', array(
            'label' => 'Your comment:',
        	'decorators' => $this->elementDecorators,
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
            )
        ));
        
        // Creates a submit button element.
        $this->addElement('image', 'submit', array(
            'ignore' => true,
        	'decorators' => $this->buttonDecorators,
            'label' => 'Create User',
        	'src' => '/img/btn_submit.gif', 
        	'class' => 'image'
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
