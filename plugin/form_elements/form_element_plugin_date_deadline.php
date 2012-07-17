<?php

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin.class.php');

/**
 *
 */
class form_element_plugin_date_deadline extends form_element_plugin {

    /**
     * @var string
     */
    public $tablename;
	public $data_entry_tablename;
	public $datetense;	//offers the form creator 'past', 'present' and 'future' options to control validation of the user input	
	
    /**
     * Constructor
     */
    function __construct() {

    	$this->tablename = "ulcc_form_plg_ddl";
    	$this->data_entry_tablename = "ulcc_form_plg_ddl_ent";
    	parent::__construct();

        //this line needs to be after call to parent construct
        $this->dbc = new form_element_plugin_date_deadline_form_db();
    }
	
	
     /**
     * TODO comment this
     *
     */
    public function load($formfield_id) {
		$formfield		=	$this->dbc->get_form_field_data($formfield_id);	
		if (!empty($formfield)) {
			$this->formfield_id	=	$formfield_id;
			$plugin		=	$this->dbc->get_form_element_plugin($formfield->formelement_id);
			$this->formelement_id=	$formfield->formelement_id;
			$pluginrecord	=	$this->dbc->get_form_element_by_formfield($this->tablename,$formfield->id);
			if (!empty($pluginrecord)) {
				$this->label			=	$formfield->label;
				$this->description		=	$formfield->description;
				$this->required			=	$formfield->required;
				$this->datetense		=	$this->datetense;
				$this->position			=	$formfield->position;
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
        $table = new $this->xmldb_table( $this->tablename );
        drop_table($table);
        
        $table = new $this->xmldb_table( $this->data_entry_tablename );
        drop_table($table);
    }
	
     /**
     *
     */
    public function audit_type() {
        return get_string('form_element_plugin_date_deadline_type','local_ulcc_form_library');
    }
    
    /**
    * function used to return the language strings for the plugin
    */
    function language_strings(&$string) {
        $string['form_element_plugin_date_deadline']		        = 'Date deadline';
        $string['form_element_plugin_date_deadline_deadline']		= 'Deadline';
        $string['form_element_plugin_date_deadline_type'] 	        = 'date deadline';
        $string['form_element_plugin_date_deadline_description']	= 'A date deadline entry element';
        $string['form_element_plugin_date_deadline_tense'] 	= 'Date tense';
        $string['form_element_plugin_date_deadline_past'] 	= 'past';
        $string['form_element_plugin_date_deadline_present'] 	= 'present';
        $string['form_element_plugin_date_deadline_future'] 	= 'future';
        $string['form_element_plugin_date_deadline_anydate'] 	= 'none of the above, or a mixture';
        
        return $string;
    }
    
    /**
    * this function returns the mform elements that will be added to a form form
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
     * Processes the data entered into the form field
     *
     * @param int       $formfield_id
     * @param int       $entry_id
     * @param object    $data
     * @return bool
     */
    function entry_process_data($formfield_id,$entry_id,$data)	{
    	return $this->entry_specific_process_data($formfield_id, $entry_id, $data);
    }


    /**
     * Handles data that has been supplied
     *
     * @param int $formfield_id the id of the parent form field
     * @param int $entry_id the id of the parent entry
     * @param object    $data the data to be saved
     * @return bool
     */
    public	function entry_specific_process_data($formfield_id,$entry_id,$data) {
		global $CFG;
		
		/*
		* parent method is fine for simple form element types
		* dd types will need something more elaborate to handle the intermediate
		* items table and foreign key
		*/
	 	
	 	$fieldname =	$formfield_id."_field";
	 	
	 	$form		=	$this->dbc->get_form_by_id($data->form_id);
	 	
	 	$title		=	(!empty($form))	? $form->name." ".get_string('form_element_plugin_date_deadline_deadline','local_ulcc_form_library') : get_string('form_element_plugin_date_deadline_deadline','local_ulcc_form_library');
	 	
	 	$event	=	$this->dbc->get_calendar_event($entry_id,$formfield_id);
	 	
 
 	 	if (empty($event))		{
	 		$event = new stdClass();
	        $event->name        = $title;
	        //link to form has been removed due to moodle encoding html and outputing it.
	        $event->description = $title;
	        $event->format      = 0;
	        $event->courseid    = 0;
	        $event->groupid     = 0;
	        $event->userid      = $data->user_id;
	        $event->modulename  = '0';
	        $event->instance    = 0;
	        $event->eventtype   = 'due';
	        $event->timestart   = $data->$fieldname;
	        $event->timeduration = time();
	        
	        $event->id = $this->dbc->save_event($event);

	        $record					=	new stdClass();
	        $record->entry_id		=	$entry_id;
	        $record->formfield_id	=	$formfield_id;
	        $record->event_id		=	$event->id;
	        $record->timemodified	=	time();	
	        $record->timecreated	=	time();	
	        
	        //create the calendar cross reference record
	        $this->dbc->create_event_cross_reference($record);
	        
	 	}   else	{
	 		$event	=	$this->dbc->get_calendar_event($entry_id,$formfield_id);
	 		if (!empty($event))	{	
	 			$event->timestart		=	$data->$fieldname;
	 			$event->timemodified	=	time();	
	 			$event->modulename  	= 	'0';
	 			$event->uuid  			= 	0;
	 			$this->dbc->update_event($event);
	 		} 
	 	}
	 	
	 	//call the parent entry_process_data function to handle saving the field value
	 	return parent::entry_process_data($formfield_id,$entry_id,$data);
		 	
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
	  public function view_data( $formfield_id,$entry_id,&$entryobj ){
	  	global $CFG;

	  	$fieldname	=	$formfield_id."_field";
	 	
	 	$entry	=	$this->dbc->get_pluginentry($this->tablename,$entry_id,$formfield_id);
 	
	 	if (!empty($entry)) {
	 		
	 		//we can only judge whether a form entry is overdue or not if the form has a state field if it does not 
	 		//we can never correctly say that the entry is overdue. 
	 		
	 		$entryrecord	=	$this->dbc->get_entry_by_id($entry_id);
	 		//check if current form has state field
	 		$has_statefield 	= $this->dbc->has_plugin_field($entryrecord->form_id,'form_element_plugin_state');
	 		$img	=	"";
	 		
	 		if (!empty($has_statefield))	{ 
	 			//check if the entry is in a unset state 
				$recordstate	=	$this->dbc->count_form_entries_with_state($entryrecord->form_id,$entryrecord->user_id,form_STATE_UNSET,false,$entry_id);
	 			if (!empty($recordstate) && $entry->value < time()) {
  			 		$img	=	 "<img src='{$CFG->wwwroot}/local/ulcc_form_library/plugin/form_elements/pix/icons/overdue.jpg' alt='' width='32px' height='32px' />";
	 			} 
	 		}
	 		$entryobj->$fieldname	=	userdate(html_entity_decode($entry->value),'%a %d %B %Y') ." ".$img;
	 	}
	  	
	 }
	 
   	/**
     * Delete a form element
     *
     * @param int $formfield_id
     */
    public function delete_form_element($formfield_id) {
    	return parent::delete_form_element($this->tablename, $formfield_id);
    }
	 
}


class form_element_plugin_date_deadline_form_db extends form_db  {


    /**
     * Returns a record from the block_cal_event table - this method is declared protected as
     * we want to make sure the parent form_db class accesses it via the __call function (thus)
     * calling encode first
     *
     * @param int $entry_id the id of the entry that the record was creared for
     * @param int $reportfield_id the id of the reportfield that the report was created for
     */
    protected function get_calendar_event($entry_id,$formfield_id)	{

        /*
        $sql	=	"SELECT		e.*
    				 FROM 		{block_ilp_cal_events} as ce,
    				 			{event} as e
    				 WHERE		e.id = ce.event_id
    				 AND		ce.formfield_id	=	{$formfield_id}
    				 AND		ce.entry_id			=	{$entry_id}";

        return $this->dbc->get_record_sql($sql);
        */
    }


    /**
     * Adds an event to the calendar of a user
     *
     * @param object $event a object containing details of an event tot be saved into a users calendar
     */
    protected function save_event($event)	{
        //we can not user add_event in moodle 2.0 as it requires the user to have persmissions to add events to the
        //calendar however this capability check can be bypassed if we use the calendar event class so we will use add_event in
        //1.9 and calendar_event class in 2.0
        global $CFG, $USER;

        if (stripos($CFG->release,"2.") !== false) {
            require_once($CFG->dirroot.'/calendar/lib.php');
            $calevent = new calendar_event($event);
            $calevent->update($event,false);

            if ($calevent !== false) {
                return $calevent->id;
            }

        } else {
            return add_event($event);
        }
    }

    /**
     *
     * Updates a calendar event with new details
     * @param object $event a object containing details of an event tot be saved into a users calendar
     */
    protected function update_event($event)	{

        global $CFG, $USER;

        if (stripos($CFG->release,"2.") !== false) {
            require_once($CFG->dirroot.'/calendar/lib.php');
            $calevent = calendar_event::load($event->id);
            return $calevent->update($event,false);
        } else {
            return update_event($event);
        }
    }

    protected function create_event_cross_reference($record) {
     //   return $this->insert_record('block_ilp_cal_events',$record);
    }



}

