<?php
require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin_itemlist.class.php');

class form_element_plugin_category extends form_element_plugin_itemlist{

	public $tablename;
	public $data_entry_tablename;
	public $items_tablename;
	public $selecttype;

    /**
     * Constructor
     */
    function __construct() {
    	$this->tablename            = "ulcc_form_plg_cat";
    	$this->data_entry_tablename = "ulcc_form_plg_cat_ent";
		$this->items_tablename      = "ulcc_form_plg_cat_items";
		$this->selecttype = FORM_OPTIONSINGLE;
		parent::__construct();
    }

    /*
    * should not be able to add a category selector if there is already one one the form
    */
    public function can_add( $form_id ){
        return !$this->dbc->element_type_exists( $form_id, $this->tablename );
    }





    public function audit_type() {
        return get_string('form_element_plugin_category_type','local_ulcc_form_library');
    }

    /**
     * this function returns the mform elements that will be added to a form form
     */
	public function entry_form(MoodleQuickForm &$mform ) {

		//create the fieldname
    	$fieldname	=	"{$this->formfield_id}_field";

    	if (!empty($this->description)) {
    		$mform->addElement('static', "{$fieldname}_desc", $this->label, strip_tags(html_entity_decode($this->description),FORM_STRIP_TAGS_DESCRIPTION));
    		$this->label = '';
    	}

		//definition for user form
		$optionlist = $this->get_option_list( $this->formfield_id );
       	$select = $mform->addElement(
				       			'select',
				     			$fieldname,
				       			$this->label,
								$optionlist,
				        		array('class' => 'form_input')
				       	 	);

        if (!empty($this->required)) $mform->addRule($fieldname, null, 'required', null, 'client');
        $mform->setType($fieldname, PARAM_RAW);
	}


    /**
     * Creates language strings used for this form element
     *
     * @param array $string the current array containing all language strings
     * @return array
     */
    function language_strings(&$string) {
        $string['form_element_plugin_category'] 			        = 'Category Select';
        $string['form_element_plugin_category_type'] 		        = 'category select';
        $string['form_element_plugin_category_description'] 	    = 'A category selector';
        $string[ 'form_element_plugin_category_optionlist' ] 	    = 'Option List';
        $string[ 'form_element_plugin_category_single' ] 	        = 'Single select';
        $string[ 'form_element_plugin_category_multi' ] 		    = 'Multi select';
        $string[ 'form_element_plugin_category_typelabel' ] 	    = 'Select type (single/multi)';

        return $string;
    }
}
