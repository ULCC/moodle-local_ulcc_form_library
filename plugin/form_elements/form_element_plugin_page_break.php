<?php

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin.class.php');

class form_element_plugin_page_break extends form_element_plugin {
	
	public $tablename;

	    /**
     * Constructor
     */
    function __construct() {
    	
    	$this->tablename = "ulcc_form_plg_pb";

    	parent::__construct();
    }
	
	
	/**
     * TODO comment this
     * called when user form is submitted
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

                //required has no relevance to a page break so always have it set to false
				$this->required			=	0;
				$this->position			=	$formfield->position;
                $this->audit_type       =   $this->audit_type();
				return true;	
			}
		}
		return false;	
    }	

	
	/**
     * create tables for this plugin
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

        $table_timemodified = new $this->xmldb_field('timemodified');
        $table_timemodified->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_timemodified);

        $table_timecreated = new $this->xmldb_field('timecreated');
        $table_timecreated->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_timecreated);

        $table_key = new $this->xmldb_key('primary');
        $table_key->$set_attributes(XMLDB_KEY_PRIMARY, array('id'));
        $table->addKey($table_key);

        $table_key = new $this->xmldb_key('pagebreakplugin_unique_formfield');
        $table_key->$set_attributes(XMLDB_KEY_FOREIGN_UNIQUE, array('formfield_id'),'local_ulcc_form_library_form_field','id');
        $table->addKey($table_key);

        if(!$this->dbman->table_exists($table)) {
            $this->dbman->create_table($table);
        }

    }

    /**
     *
     */
    public function uninstall() {
        $table = new $this->xmldb_table( $this->tablename );
        drop_table($table);
    }
	
     /**
     *
     */
    public function audit_type() {
        return get_string('form_element_plugin_pagebreak_type','local_ulcc_form_library');
    }
    
    /**
    * function used to return the language strings for the plugin
    */
    function language_strings(&$string) {
        $string['form_element_plugin_page_break'] 		= 'Page break';
        $string['form_element_plugin_page_break_type'] = 'Page break';
        $string['form_element_plugin_page_break_description'] = 'A page break';
        
        return $string;
    }

   	/**
     * Delete a form element
     */
    public function delete_form_element($formfield_id) {
		$formfield		=	$this->dbc->get_form_field_data($formfield_id);
        $extraparams = array(
            'audit_type' => $this->audit_type(),
            'label' => $formfield->label,
            'description' => $formfield->description,
            'id' => $formfield_id
        );
    	return parent::delete_form_element( $this->tablename, $formfield_id, $extraparams );
    }
    
    /**
    * this function returns the mform elements taht will be added to a form form
	*
    */
    public	function entry_form( &$mform ) {
        $mform->addElement('hidden', "formsession[{$this->formfield_id}]",'');
	 }
	 
	/**
	* handle user input
	**/
	 public	function entry_specific_process_data($formfield_id,$entry_id,$data) {
        //nothing to do in the page break class
	 }

    /**
     * page breaks can not be processed
     * @return bool
     * */
    public function is_processable()	{
        return false;
    }

    /**
     * Page breaks are not viewable
     * @return bool
     */
    public function is_viewable()	{
        return false;
    }

    /**
     * Page breaks are not configurable
     *
     * @return bool
     */
    public function is_configurable()	{
        return false;
    }
	 
	 
}
