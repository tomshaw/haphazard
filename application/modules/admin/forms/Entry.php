<?php
/**
 * The main user administration form for creating and editing users.
 *
 */
class Admin_Form_Entry extends Zend_Form
{
	private $elementDecorators = array(
        'ViewHelper',
        //array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'element')),
        'Label',
        array(array('row' => 'HtmlTag'), array('tag' => 'li')),
    );
 
    private $buttonDecorators = array(
        'ViewHelper',
        array(array('data' => 'HtmlTag'), array('tag' => 'fieldset', 'class' => 'submit-buttons')),
        //array(array('row' => 'HtmlTag'), array('tag' => 'li')),
    );
 
    private $captchaDecorators = array(
        'Label',
        array(array('row' => 'HtmlTag'), array('tag' => 'li'))
    );
    
    private $hiddenDecorators = array(
        'ViewHelper',
        array(array('data' => 'HtmlTag'), array('tag' => 'span')),
    );
	/**
	 * Initiates the main admin entry form.
	 *
	 */
    public function init()
    {
    	// Sets the form name to form. 
        $this->setName('form');
        
        // Set the method for the display form to POST
        $this->setMethod('post');
        	
        // Creates a user name element.
        // FIXME Review the validators here.
        $this->addElement('text', 'title', array(
            'filters' => array('StringTrim','StripTags'),
            'validators' => array(
                array('StringLength', false, array(0, 50)),
            ),
            'required' => true,
            'label' => 'Entry title',
            'attribs' => array(
            	'size' => 45
            ),
            'class' => 'input-medium',
            //'decorators' => $this->elementDecorators,
        ));

        // Adds the main body element.
        $this->addElement('textarea', 'body', array(
            'label' => 'Entry body',
            'required' => false,
            'validators' => array(
                array(
                	'validator' => 'StringLength', 
                	'options' => array(0, 1000000)
                )
            ),
            'attribs' => array(
            	'cols' => 75,
            	'rows' => 15
            ),
            'class' => 'input-medium',
            //'decorators' => $this->elementDecorators,
        ));

        // adds the body continued element.
        $this->addElement('textarea', 'continued', array(
            'label' => 'Entry continued',
            'required' => false,
            'validators' => array(
                array(
                	'validator' => 'StringLength', 
                	'options' => array(0, 1000000)
                )
            ),
            //'decorators' => $this->elementDecorators,
            'attribs' => array(
            	'cols' => 75,
            	'rows' => 15
            ),
            'class' => 'input-medium',
        ));
        
        // Creates a user active select element.
        $this->addElement('checkbox', 'draft', array(
            'required' => false,
            'multiOptions' => array(1 => 'Yes', 0 => 'No'),
            'label' => 'Is draft',
        	'value' => 1,
        	//'decorators' => $this->elementDecorators,
        	'class' => 'input-checkbox'
        ));
        
        // Creates a user active select element.
        $this->addElement('select', 'comments', array(
            'required' => false,
            'multiOptions' => array(1 => 'Yes', 0 => 'No'),
            'label' => 'Allow comments',
        	'value' => 1,
        	//'decorators' => $this->elementDecorators,
        ));
        
        // Creates a user active select element.
        $this->addElement('select', 'trackbacks', array(
            'required' => false,
            'multiOptions' => array(1 => 'Yes', 0 => 'No'),
            'label' => 'Allow trackbacks',
        	'value' => 1,
        	//'decorators' => $this->elementDecorators,
        ));
        
        // Creates a user active select element.
        $this->addElement('select', 'approved', array(
            'required' => true,
            'multiOptions' => array(1 => 'Yes', 0 => 'No'),
            'label' => 'Entry approved',
        	'value' => 1,
        	//'decorators' => $this->elementDecorators,
        ));
        
        // Adds a entry id element. When creating a new row we remove the element from the controller.
        $this->addElement('hidden', 'id', array(
            'filters' => array('StringTrim'),
            'validators' => array('Int'),
            'required' => false,
        	'decorators' => $this->hiddenDecorators,
        ));
        
        // Adds a user id element. When creating a new row we remove the element from the controller.
        $this->addElement('hidden', 'user_id', array(
            'filters' => array('StringTrim'),
            'validators' => array('Int'),
            'required' => false,
        	'decorators' => $this->hiddenDecorators,
        ));
        
        // Adds a user id element. When creating a new row we remove the element from the controller.
        $this->addElement('hidden', 'parent_id', array(
            'filters' => array('StringTrim'),
            'validators' => array('Int'),
            'required' => false,
        	'decorators' => $this->hiddenDecorators,
        ));
        
        // Creates a submit button element.
        $this->addElement('submit', 'submit', array(
            'ignore' => true,
        	'decorators' => $this->buttonDecorators,
            'label' => 'Create Blog Entry',
        	//'src' => '/img/btn_submit.gif', 
        	'class' => 'btn btn-primary'
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