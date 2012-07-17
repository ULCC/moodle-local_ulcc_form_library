<?php
require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin_itemlist.class.php');

class form_element_plugin_course extends form_element_plugin_itemlist{

	public $tablename;
	public $data_entry_tablename;
	public $items_tablename;	//false - this class will use the course table for its optionlist
	public $selecttype;
	
    /**
     * Constructor
     */
    function __construct() {
    	
    	parent::__construct();
    	$this->tablename = "ulcc_form_plg_crs";
    	$this->data_entry_tablename = "ulcc_form_plg_crs_ent";
		$this->items_tablename = false;		//items tablename is the course table
    	$this->selecttype = FORM_OPTIONSINGLE;
		$this->optionlist = false;
        $this->external_items_table = 'course';
        $this->external_items_keyfield = 'id';
    }


    /**
     * Get the list of options for the given form field. This incarnation has been changed to provide a list of
     * all courses that the user is in.
     *
     * @param int   $formfield_id   the id of the form field whose list of options will be returned
     * @param int   $user_id        the id of the user whose course list will be returned
     * @return array
     */

    function get_option_list( $formfield_id,$user_id=false ){
		$courseoptions = array();
		
		$courseoptions['-1']	=	get_string('form_element_plugin_course_personal','local_ulcc_form_library');
		$courseoptions[0]		=	get_string('form_element_plugin_course_allcourses','local_ulcc_form_library');
		//check if the user_id has been set 
		$courselist = (!empty($user_id)) ? $this->dbc->get_user_courses($user_id) : $this->dbc->get_courses();
		
		foreach( $courselist as $c ){
			$courseoptions[ $c->id ] = $c->fullname;
		}
		
		return $courseoptions;
	}
    
	
	/*
	* get the list options with which to populate the edit element for this list element
    * this type is unusual in that the item table is 'course' (not the usual item table for list elements)
    * so we have to call plugin_data_item_exists with extra args
	*
	* @param    object  $formfield the form field object
	*/
	public function return_data( &$formfield ){
        global $CFG;
        $item_table = $CFG->prefix . 'course';
		$data_exists = $this->dbc->plugin_data_item_exists( $this->tablename, $formfield->id , $item_table, '', 'id' );
		if( empty( $data_exists ) ){
			//if no, get options list
			$formfield->optionlist = $this->get_option_list_text( $formfield->id );
		}
		else{
			$formfield->existing_options = $this->get_option_list_text( $formfield->id , '<br />' );
		}
	}

    /**
     * The type that will be formed when logging actions created by this form element
     */
    public function audit_type() {
        return get_string('form_element_plugin_course_type','local_ulcc_form_library');
    }
    
    
	 /**
	  * places entry data formated for viewing for the form field given  into the  
	  * entryobj given by the user. By default the entry_data function is called to provide
	  * the data. This is a specific instance of the view_data function for the 
	  * 
	  * @param int $formfield_id the id of the formfield that the entry is attached to 
	  * @param int $entry_id the id of the entry
	  * @param object $entryobj an object that will add parameters to
	  */
	  public function view_data( $formfield_id,$entry_id,&$entryobj ){
	  		$fieldname	=	$formfield_id."_field";
	 		
	 		$entry	=	$this->dbc->get_pluginentry($this->tablename,$entry_id,$formfield_id,false);

			if (!empty($entry)) {
		 		$fielddata	=	array();
		 		$comma	= "";
		 		
			 	//loop through all of the data for this entry in the particular entry		 	
			 	foreach($entry as $e) {
			 		if (!empty($e->value)) {
			 			$course	=	$this->dbc->get_course($e->value);
			 			$entryobj->$fieldname	.=	"{$comma}{$course->shortname}";
			 			$comma	=	",";
			 		}
			 	}
	 		}
	  }
	 /**
	  * places entry data for the form field given into the entryobj given by the user 
	  * 
	  * @param int $formfield_id the id of the formfield that the entry is attached to 
	  * @param int $entry_id the id of the entry
	  * @param object $entryobj an object that will add parameters to
	  */
	 public function entry_data( $formfield_id,$entry_id,&$entryobj ){
	 	//this function will suffix for 90% of plugins who only have one value field (named value) i
	 	//in the _ent table of the plugin. However if your plugin has more fields you should override
	 	//the function 
	 	
		//default entry_data 	
		$fieldname	=	$formfield_id."_field";
	 	
	 	
	 	$entry	=	$this->dbc->get_pluginentry($this->tablename,$entry_id,$formfield_id,false);
 
		if (!empty($entry)) {
		 	$fielddata	=	array();

		 	//loop through all of the data for this entry in the particular entry		 	
		 		$fielddata[]	=	$entry->value;
		 	
		 	//save the data to the objects field
	 		$entryobj->$fieldname	=	$fielddata;
	 	}
	 }
		/**
	    * this function saves the data entered on a entry form to the plugins _entry table
		* the function expects the data object to contain the id of the entry (it should have been
		* created before this function is called) in a param called id. 
		* as this is a select element, possibly a multi-select, we have to allow
		* for the possibility that the input is an array of strings
	    */
	  	public	function entry_process_data($formfield_id,$entry_id,$data) {
	 	
	  		$result	=	true;
	  		
		  	//create the fieldname
			$fieldname =	$formfield_id."_field";
	
		 	//get the plugin table record that has the formfield_id 
		 	$pluginrecord	=	$this->dbc->get_form_element_record($this->tablename,$formfield_id);
		 	if (empty($pluginrecord)) {
		 		print_error('pluginrecordnotfound');
		 	}
		 	
		 	//check to see if a entry record already exists for the formfield in this plugin
            $multiple = !empty( $this->items_tablename );
		 	$entrydata 	=	$this->dbc->get_pluginentry($this->tablename, $entry_id,$formfield_id,$multiple);
		 	
		 	//if there are records connected to this entry in this formfield_id 
			if (!empty($entrydata)) {
				//delete all of the entries
                    $extraparams = array( 'audit_type' => $this->audit_type() );
					$this->dbc->delete_element_record_by_id($this->data_entry_tablename,$entrydata->id,$extraparams);

			}  
		 	
			//create new entries
			$pluginentry			=	new stdClass();
            $pluginentry->audit_type = $this->audit_type(); //send the audit type through for logging purposes
			$pluginentry->entry_id  = 	$entry_id;
	 		$pluginentry->value		=	$data->$fieldname;

			if( is_string( $pluginentry->value ))	{
	 		    $state_item				=	$this->dbc->get_state_item_id($this->tablename,$pluginrecord->id,$data->$fieldname, $this->external_items_keyfield, $this->external_items_table );
	 		    $pluginentry->parent_id	=	$pluginrecord->id;	
	 			$result	= $this->dbc->create_plugin_entry($this->data_entry_tablename,$pluginentry);
			} else if (is_array( $pluginentry->value ))	{
                $pluginentry->parent_id = $formfield_id;
				$result	=	$this->write_multiple( $this->data_entry_tablename, $pluginentry );
			}

			return	$result;
	 }
	
   /**
    * this function returns the mform elements that will be added to a form form
	*
    */
    public function entry_form( &$mform ) {
    	
    	global	$PARSER;
    	
    	
    	//get the id of the course that is currently being used
		$user_id = optional_param('user_id', NULL, PARAM_INT);

		//get the id of the course that is currently being used
		$course_id = optional_param('course_id', NULL, PARAM_INT);
    	
    	//create the fieldname
    	$fieldname	=	"{$this->formfield_id}_field";
    	
		//definition for user form
		$optionlist = $this->get_option_list( $this->formfield_id, $user_id );

    	if (!empty($this->description)) {
    		$mform->addElement('static', "{$fieldname}_desc", $this->label, strip_tags(html_entity_decode($this->description),FORM_STRIP_TAGS_DESCRIPTION));
    		$this->label = '';
    	} 

    	
    	//text field for element label
        $select = &$mform->addElement(
            'select',
            $fieldname,
            $this->label,
	    	$optionlist,
            array('class' => 'form_input')
        );
		
        if( FORM_OPTIONMULTI == $this->selecttype ){
			$select->setMultiple(true);
		}
        
		if (!empty($course_id)) $select->setValue($course_id);
		
		
        if (!empty($this->required)) $mform->addRule($fieldname, null, 'required', null, 'client');
        $mform->setType('label', PARAM_RAW);

    }

    /**
     * Creates language strings used for this form element
     *
     * @param array $string the current array containing all language strings
     * @return array
     */
    function language_strings(&$string) {
        $string['form_element_plugin_course'] 			        = 'Select';
        $string['form_element_plugin_course_type'] 		        = 'course select';
        $string['form_element_plugin_course_description'] 	    = 'A course selector';
        $string[ 'form_element_plugin_course_optionlist' ] 	    = 'Option List';
        $string[ 'form_element_plugin_course_single' ] 		    = 'Single select';
        $string[ 'form_element_plugin_course_multi' ] 		    = 'Multi select';
        $string[ 'form_element_plugin_course_typelabel' ] 	    = 'Select type (single/multi)';
        $string[ 'form_element_plugin_course_noparticular' ] 	= 'no particular course';
        $string[ 'form_element_plugin_course_personal' ] 	    = 'Personal';
        $string[ 'form_element_plugin_course_allcourses' ] 	    = 'All courses';

        return $string;
    }



}
