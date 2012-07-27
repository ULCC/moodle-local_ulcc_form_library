<?php
require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin.class.php');

class form_element_plugin_free_html extends form_element_plugin {

    /**
     * @var string
     */
    public $tablename;
    /**
     * @var string
     */
    public $data_entry_tablename;

    function __construct() {
    	$this->tablename = "ulcc_form_plg_fre";
    	$this->data_entry_tablename = "ulcc_form_plg_fre_ent";
    	parent::__construct();
    }

    /**
     * @param $formfield_id
     * @return bool
     */
    public function load($formfield_id) {
		$formfield		=	$this->dbc->get_form_field_data($formfield_id);
		if (!empty($formfield)) {
			$this->formfield_id	=	$formfield_id;
			$this->formelement_id	=	$formfield->formelement_id;
			$this->label			=	$formfield->label;
			$this->description		=	$formfield->description;
			$this->required				=	0;
			$this->position			=	$formfield->position;
            $this->audit_type       =   $this->audit_type();
			return true;	
        }
		return false;	
    }

    /*
    * essential delete method
    * @param int $formfield_id
    * @return boolean
    */
    /**
     * @param $formfield_id
     * @return bool
     */
    public function delete_form_element($formfield_id, $tablename=null, $extraparams=null) {

		$formfield		=	$this->dbc->get_form_field_data($formfield_id);
        $extraparams = array(
            'audit_type' => $this->audit_type(),
            'label' => $formfield->label,
            'description' => $formfield->description,
            'id' => $formfield_id
        );
    	return parent::delete_form_element($formfield_id, $this->tablename, $extraparams);
    }
    /**
    * this function returns the mform elements that will be added to a form form
	*
    */
    public	function entry_form( &$mform ) {
    	
    	$fieldname	=	"{$this->formfield_id}_field";

    	//html field for element label
        $mform->addElement(
            'html',
            html_entity_decode($this->description)
        );

        if (!empty($this->required)) $mform->addRule($fieldname, null, 'required', null, 'client');
        $mform->setType($fieldname, PARAM_RAW);
    }

    /**
     * @return string
     */
    public function audit_type() {
        return get_string('form_element_plugin_free_html_type','local_ulcc_form_library');
    }

    /**
     * @param array $string
     */
    function language_strings(&$string) {
        $string['form_element_plugin_free_html_type'] 		= 'Free markup';
        $string['form_element_plugin_free_html_description']	= 'Free HTML';
        $string['form_element_plugin_free_html_contents']	= 'Contents (any valid HTML)';
        $string[ 'form_element_plugin_free_html_markup_required' ]	= 'You need to enter some markup in the contents field';
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

        $table_key = new $this->xmldb_key('textplugin_unique_formfield');
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
        $table_title->$set_attributes(XMLDB_TYPE_TEXT);
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
     * Can this field be processed
     *
     * @return bool
     */
    public function is_processable()	{
    	return false;
    }

    /**
     * is this form element viewable
     *
     * @return bool
     */
    public function is_viewable()	{
    	return false;
    }

    /**
     *
     */
    public function uninstall() {
        $table = new $this->xmldb_table( $this->tablename );
        drop_table($table);
        
        $table = new $this->xmldb_table( $this->data_entry_tablename );
        drop_table($table);
    }
    

}
