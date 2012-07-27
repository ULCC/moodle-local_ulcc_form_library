<?php

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin.class.php');

class form_element_plugin_file extends form_element_plugin {
	
	public $tablename;
	public $data_entry_tablename;
	public $acceptedtypes;
    public $maxsize;
    public $maxfiles;
    public $multiple;
	
	    /**
     * Constructor
     */
    function __construct() {
    	
    	$this->tablename = "ulcc_form_plg_file";
    	$this->data_entry_tablename = "ulcc_form_plg_file_ent";
    	
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
				$this->required			=	$formfield->required;
				$this->acceptedtypes	=	unserialize(base64_decode($pluginrecord->acceptedtypes));
                $this->maxsize          =   $pluginrecord->maxsize;
                $this->multiple         =   $pluginrecord->multiple;
                $this->maxfiles          =   $pluginrecord->maxfiles;
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
        
        $table_acceptedtypes = new $this->xmldb_field('acceptedtypes');
        $table_acceptedtypes->$set_attributes(XMLDB_TYPE_TEXT, 1500, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_acceptedtypes);

        $table_maxsize = new $this->xmldb_field('maxsize');
        $table_maxsize->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_maxsize);

        $table_maxfiles = new $this->xmldb_field('maxfiles');
        $table_maxfiles->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_maxfiles);

        $table_multiple = new $this->xmldb_field('multiple');
        $table_multiple->$set_attributes(XMLDB_TYPE_INTEGER, 1, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_multiple);

        $table_timemodified = new $this->xmldb_field('timemodified');
        $table_timemodified->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_timemodified);

        $table_timecreated = new $this->xmldb_field('timecreated');
        $table_timecreated->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_timecreated);

        $table_key = new $this->xmldb_key('primary');
        $table_key->$set_attributes(XMLDB_KEY_PRIMARY, array('id'));
        $table->addKey($table_key);

        $table_key = new $this->xmldb_key('fileplugin_unique_formfield');
        $table_key->$set_attributes(XMLDB_KEY_FOREIGN_UNIQUE, array('formfield_id'),'local_ulcc_form_library_form_field','id');
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
        $table = new $this->xmldb_table( $this->tablename );
        drop_table($table);
        
        $table = new $this->xmldb_table( $this->data_entry_tablename );
        drop_table($table);
    }
	
     /**
     *
     */
    public function audit_type() {
        return get_string('form_element_plugin_file_type','local_ulcc_form_library');
    }
    
    /**
    * function used to return the language strings for the plugin
    */
    function language_strings(&$string) {
        $string['form_element_plugin_file'] 		    = 'File upload';
        $string['form_element_plugin_file_type']        = 'File upload';
        $string['form_element_plugin_file_description'] = 'A file upload';
        $string['form_element_plugin_file_acceptedfiles'] = 'Accepted types';
        $string['form_element_plugin_file_maxsize']       = 'Maximum file size';
        $string['form_element_plugin_file_multiple']       = 'Multiple Files';
        $string['form_element_plugin_file_maxfiles']       = 'Maximum Files (if multiple files selected)';

        return $string;
    }

   	/**
     * Delete a form element
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
    	if (!empty($this->description)) {
    		$mform->addElement('static', "{$fieldname}_desc", $this->label, strip_tags(html_entity_decode($this->description),FORM_STRIP_TAGS_DESCRIPTION));
    		$this->label = '';
    	}

        if (empty($this->multiple))    {

            $mform->addElement('filepicker',
                                $fieldname,
                                $this->label,
                                null

                               );
        }   else    {
            $mform->addElement('filemanager',
                                $fieldname,
                                $this->label,
                                null,
                                array('subdirs' => 0,
                                      'maxbytes' => $this->maxbytes, '
                                       maxfiles' => $this->maxfiles,
                                       'accepted_types' => $this->acceptedtypes )
                              );
        }

        if (!empty($this->required)) $mform->addRule($fieldname, null, 'required', null, 'client');
	 }


   function entry_process_data($formfield_id,$entry_id,$data)   {
       return $this->entry_specific_process_data($formfield_id,$entry_id,$data);
   }

	/**
	* handle user input
	**/
	 public	function entry_specific_process_data($formfield_id,$entry_id,$data) {
         global $USER;

		$fieldname =	$formfield_id."_field";

         //get the value for this element in the data returned. The value is the id of the files save location
         $draftid = $data->$fieldname;

         if (!empty($draftid) && !empty($_FILES))  {

             //instantiate file storage
             $fs = get_file_storage();

             //get the current users context as this is where the file will have been saved
             $context = get_context_instance(CONTEXT_USER, $USER->id);

             //check if the file exists
             if (!$files = $fs->get_area_files($context->id, 'user', 'draft', $draftid, 'id DESC', false)) {
                 print_error('filenotfound');
             }

             //get system context as this is the area of file storage we will be saving the file into
             $sitecontext   =   context_system::instance();

             $test = file_save_draft_area_files($draftid,$sitecontext->id,'ulcc_form_library','form_element_plugin_file',$draftid);

  		    return parent::entry_process_data($formfield_id,$entry_id,$data);
         } else {
             return true;
         }
	 }


    public function return_data(&$data)   {
        $data->acceptedtypes    =   unserialize(base64_decode($data->acceptedtypes));
    }

    public function entry_data( $formfield_id,$entry_id,&$entryobj ){

        //default entry_data
        $fieldname	=	$formfield_id."_field";

        $entry	=	$this->dbc->get_form_element_entry($this->tablename,$entry_id,$formfield_id);
        if (!empty($entry)) {
            $entryobj->$fieldname	=	html_entity_decode($entry->value, ENT_QUOTES, 'UTF-8');

            $sitecontext   =   context_system::instance();

            //prepare the file to be used
            file_prepare_draft_area($entryobj->$fieldname, $sitecontext->id, 'ulcc_form_library', 'form_element_plugin_file', $entryobj->$fieldname);
        }
    }


    public function view_data( $formfield_id,$entry_id,&$entryobj ){
        global $CFG;

        $fieldname	=	$formfield_id."_field";

        $sitecontext   =   context_system::instance();

        $fs = get_file_storage();

        $entry	=	$this->dbc->get_form_element_entry($this->tablename,$entry_id,$formfield_id);

        $entryobj->$fieldname	=	html_entity_decode($entry->value, ENT_QUOTES, 'UTF-8');

        $files = $fs->get_area_files($sitecontext->id, 'ulcc_form_library', 'form_element_plugin_file',$entryobj->$fieldname);

        $list = array();


        foreach ($files as $file) {
            if ($file->get_filename() !== '.')   {
                $url = "{$CFG->wwwroot}/local/ulcc_form_library/filedownloads.php/{$file->get_contextid()}/ulcc_form_library/form_element_plugin_file}";
                $filename = $file->get_filename();
                $fileurl = $url.$file->get_filepath().$file->get_itemid().'/'.$filename;
                $out[] = html_writer::link($fileurl, $filename);
            }
        }

        $br = html_writer::empty_tag('br');

        $entryobj->$fieldname   =   implode($br, $out);
    }



	 
}
