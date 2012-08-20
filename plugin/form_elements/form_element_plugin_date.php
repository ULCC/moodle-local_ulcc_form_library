<?php

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin.class.php');

class form_element_plugin_date extends form_element_plugin {

	public $tablename;
	public $data_entry_tablename;
	public $datetense;	//offers the form creator 'past', 'present' and 'future' options to control validation of the user input	
	
    /**
     * Constructor
     */
    function __construct() {
    	$this->tablename = "ulcc_form_plg_dat";
    	$this->data_entry_tablename = "ulcc_form_plg_dat_ent";
    	parent::__construct();
    }
	
	
	/**
     * TODO comment this
     *
     */
    public function load($formfield_id) {
		$formfield		=	$this->dbc->get_form_field_data($formfield_id);
		if (!empty($formfield)) {
			//set the formfield_id var
			$this->formfield_id	=	$formfield_id;
			
			//get the record of the plugin used for the field 
			$plugin		=	$this->dbc->get_form_element_plugin($formfield->formelement_id);
						
			$this->formelement_id	=	$formfield->formelement_id;
			
			//get the form element record for the formfield 
			$pluginrecord	=	$this->dbc->get_form_element_by_formfield($this->tablename,$formfield->id);
			
			if (!empty($pluginrecord)) {
				$this->label			=	$formfield->label;
				$this->description		=	$formfield->description;
				$this->required			=	$formfield->required;
				$this->datetense		=	$this->datetense;
				$this->position			=	$formfield->position;
				return true;	
			}
		}
		return false;	
    }		

	
	/**
     *
     */
    public function install() {
        global $CFG, $DB;

        // create the table to store form fields
        $table = new $this->xmldb_table( $this->tablename );
        $set_attributes = method_exists($this->xmldb_key, 'set_attributes') ? 'set_attributes' : 'setAttributes';

        $table_id = new $this->xmldb_field('id');
        $table_id->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addField($table_id);
        
        $table_form = new $this->xmldb_field('formfield_id');
        $table_form->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_form);
        
        $table_datetense = new $this->xmldb_field('datetense');
        $table_datetense->$set_attributes(XMLDB_TYPE_INTEGER, 1, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_datetense);
        
        $table_timemodified = new $this->xmldb_field('timemodified');
        $table_timemodified->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_timemodified);

        $table_timecreated = new $this->xmldb_field('timecreated');
        $table_timecreated->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_timecreated);

        $table_key = new $this->xmldb_key('primary');
        $table_key->$set_attributes(XMLDB_KEY_PRIMARY, array('id'));
        $table->addKey($table_key);

        $table_key = new $this->xmldb_key('date_unique_formfield');
        $table_key->$set_attributes(XMLDB_KEY_FOREIGN_UNIQUE, array('formfield_id'),'ulcc_form_lib_form_field','id');
        $table->addKey($table_key);

        if(!$this->dbman->table_exists($table)) {
            $this->dbman->create_table($table);
        }
        
	    // create the new table to store responses to fields
        $table = new $this->xmldb_table( $this->data_entry_tablename );
        $set_attributes = method_exists($this->xmldb_key, 'set_attributes') ? 'set_attributes' : 'setAttributes';

        $table_id = new $this->xmldb_field('id');
        $table_id->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addField($table_id);
        
        $table_title = new $this->xmldb_field('value');
        $table_title->$set_attributes(XMLDB_TYPE_CHAR, 255, null, null);
        $table->addField($table_title);

        $table_form = new $this->xmldb_field('entry_id');
        $table_form->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_form);
        
        $table_maxlength = new $this->xmldb_field('parent_id');
        $table_maxlength->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_maxlength);
        
        $table_timemodified = new $this->xmldb_field('timemodified');
        $table_timemodified->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_timemodified);

        $table_timecreated = new $this->xmldb_field('timecreated');
        $table_timecreated->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_timecreated);

        $table_key = new $this->xmldb_key('primary');
        $table_key->$set_attributes(XMLDB_KEY_PRIMARY, array('id'));
        $table->addKey($table_key);
        
       	$table_key = new $this->xmldb_key($this->tablename.'_foreign_key');
        $table_key->$set_attributes(XMLDB_KEY_FOREIGN, array('parent_id'), $this->tablename ,'id');
        $table->addKey($table_key);
        
        
        if(!$this->dbman->table_exists($table)) {
            $this->dbman->create_table($table);
        }
    }

    /**
     *
     */
    public function uninstall() {
        $table = new $this->xmldb_table( $this->data_entry_tablename );
        drop_table($table);
        
        $table = new $this->xmldb_table( $this->tablename );
        drop_table($table);
    }
	
     /**
     *
     */
    public function audit_type() {
        return get_string('form_element_plugin_date_type','local_ulcc_form_library');
    }

    /**
     * Creates language strings used for this form element
     *
     * @param array $string the current array containing all language strings
     * @return array
     */
    function language_strings(&$string) {
        $string['form_element_plugin_date'] 		        = 'Date selector';
        $string['form_element_plugin_date_type'] 	        = 'date selector';
        $string['form_element_plugin_date_description'] 	= 'A date entry element';
        $string['form_element_plugin_date_tense'] 	        = 'Date tense';
        $string['form_element_plugin_date_past'] 	        = 'past';
        $string['form_element_plugin_date_present'] 	    = 'present';
        $string['form_element_plugin_date_future'] 	        = 'future';
        $string['form_element_plugin_date_anydate']	        = 'none of the above, or a mixture';

        return $string;
    }

   	/**
     * Delete a form element
     */
    public function delete_form_element($formfield_id, $tablename=null, $extraparams=null) {
    	return parent::delete_form_element( $formfield_id, $this->tablename);
    }
    
    /**
    * this function returns the mform elements taht will be added to a form form
	*
    */
    public	function entry_form( &$mform ) {
    	//create the fieldname
    	$fieldname	=	"{$this->formfield_id}_field";

    	if (!empty($this->description)) {
    		$mform->addElement('static', "{$fieldname}_desc", $this->label, strip_tags(html_entity_decode($this->description),FORM_STRIP_TAGS_DESCRIPTION));
    		$this->label = '';
    	} 
    	
    	//text field for element label
        $mform->addElement(
            'date_selector',
            $fieldname,
            $this->label,
            array('class' => 'form_input', 'optional' => false )
        );
    
        
        if (!empty($this->required)) $mform->addRule($fieldname, null, 'required', null, 'client');
	
        //@todo decide correct PARAM type for date element
        $mform->setType($fieldname, PARAM_RAW);

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
	 
	 /**
	 * places entry data formated for viewing for the form field given  into the
	 * entryobj given by the user. By default the entry_data function is called to provide
	 * the data. Any child class which needs to have its data formated should override this
	 * function.
	 *
	 * @param int $formfield_id the id of the formfield that the entry is attached to
	 * @param int $entry_id the id of the entry
	 * @param object $entryobj an object that will add parameters to
	 */
	 public function view_data($formfield_id, $entry_id, &$entryobj,$returnvalue=false){
	  	$fieldname	=	$formfield_id."_field";
	 	
	 	$entry	=	$this->dbc->get_pluginentry($this->tablename,$entry_id,$formfield_id);
	 	if (!empty($entry)) {
	 		$entryobj->$fieldname	=	userdate(html_entity_decode($entry->value),'%a %d %B %Y');
	 	}
	 }
}


