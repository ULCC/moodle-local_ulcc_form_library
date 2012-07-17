<?php

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin_itemlist.class.php');

class form_element_plugin_dd extends form_element_plugin_itemlist{
	
	public $tablename;
	public $data_entry_tablename;
	public $items_tablename;
	protected $selecttype;	//1 for single, 2 for multi
	protected $id;		//loaded from pluginrecord
	
    /**
     * Constructor
     */
    function __construct() {
    	$this->tablename 			= "ulcc_form_plg_dd";
    	$this->data_entry_tablename = "ulcc_form_plg_dd_ent";
		$this->items_tablename 		= "ulcc_form_plg_dd_items";
	    parent::__construct();
    }
	
	
	/**
     * Loads the data needed to display this field
     * beware - different from parent method because of variable select type
     * radio and other single-selects inherit from parent
     * 
     * 
     */
    public function load($formfield_id) {
		$formfield		=	$this->dbc->get_form_field_data($formfield_id);	
		if (!empty($formfield)) {
			$this->formfield_id	=	$formfield_id;
			$this->formelement_id	=	$formfield->formelement_id;
			$plugin			=	$this->dbc->get_form_element_plugin($formfield->formelement_id);
			$pluginrecord		=	$this->dbc->get_form_element_by_formfield($this->tablename,$formfield->id);
			if (!empty($pluginrecord)) {
				$this->id				=	$pluginrecord->id;
				$this->label			=	$formfield->label;
				$this->description		=	$formfield->description;
				$this->required				=	$formfield->required;
				$this->position			=	$formfield->position;
				$this->selecttype		=	$pluginrecord->selecttype;

			}
		}
		return false;	
    }	

	

    public function audit_type() {
        return get_string('form_element_plugin_dd_type','local_ulcc_form_library');
    }
    
    /**
    * function used to return the language strings for the plugin
    */
    function language_strings(&$string) {
        $string['form_element_plugin_dd'] 				= 'Select';
        $string['form_element_plugin_dd_type'] 			= 'select box';
        $string['form_element_plugin_dd_description'] 	= 'A drop-down selector';
		$string[ 'form_element_plugin_dd_optionlist' ] 	= 'Option List';
		$string[ 'form_element_plugin_dd_single' ] 		= 'Single select';
		$string[ 'form_element_plugin_dd_multi' ] 		= 'Multi select';
		$string[ 'form_element_plugin_dd_typelabel' ] 			= 'Select type (single/multi)';
		$string[ 'form_element_plugin_dd_existing_options' ] 	= 'existing options';
		$string[ 'form_element_plugin_error_item_key_exists' ]	= 'The following key already exists in this element';
		$string[ 'form_element_plugin_error_duplicate_key' ]		= 'Duplicate key';
	        
        return $string;
    }

	/**
	* handle user input
	**/
	 public	function entry_specific_process_data($formfield_id,$entry_id,$data) {
        /*
        * parent method is fine for simple form element types
        * dd types will need something more elaborate to handle the intermediate
        * items table and foreign key
        */
         return $this->entry_process_data($formfield_id,$entry_id,$data);
	 }
}

