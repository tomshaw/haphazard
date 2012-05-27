<?php
/**
 * The main user administration form for creating and editing users.
 *
 */
class Admin_Form_EntryDelete extends Zend_Form
{
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

        // Adds a entry id element. When creating a new row we remove the element from the controller.
        $this->addElement('hidden', 'id', array(
            'filters' => array('StringTrim'),
            'validators' => array('Int'),
            'required' => true,
        ));
        
        // Creates a submit button element.
        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => 'Delete Entry',
        ));

        // Adds a hash CSRF protection layer.
        $this->addElement('hash', 'csrf', array(
            'ignore' => true,
        ));
    }  
}
?>