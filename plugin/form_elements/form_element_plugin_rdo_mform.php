<?php

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin_itemlist_mform.class.php');
require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin_itemlist.class.php');

class form_element_plugin_rdo_mform  extends form_element_plugin_itemlist_mform {
	
	  	
	function __construct($form_id,$formelement_id,$creator_id,$moodleplugintype,$moodlepluginname,$formfield_id=null) {
		parent::__construct($form_id,$formelement_id,$creator_id,$moodleplugintype,$moodlepluginname,$formfield_id);
   		$this->tablename = "ulcc_form_plg_rdo";
    	$this->data_entry_tablename = "ulcc_form_plg_rdo_ent";
		$this->items_tablename = "ulcc_form_plg_rdo_items";
	}
	
	protected function specific_definition($mform) {
		
		/**
		textarea element to contain the options the admin wishes to add to the user form
		admin will be instructed to insert value/label pairs in the following plaintext format:
		value1:label1\nvalue2:label2\nvalue3:label3
		or some such
		default option could be identified with '[default]' in the same line
		*/
		
		$mform->addElement(
			'textarea',
			'optionlist',
			get_string( 'form_element_plugin_dd_optionlist', 'local_ulcc_form_library' ),
			array('class' => 'form_input')
	    );

		//admin must specify at least 1 option, with at least 1 character
        $mform->addRule('optionlist', null, 'minlength', 1, 'client');
		//@todo should we insist on a default option being chosen ?

        //added the below so that exisiting options can be seen
        $mform->addElement(
              'static',
              'existing_options',
              get_string( 'form_element_plugin_dd_existing_options' , 'local_ulcc_form_library' ),
              ''
        );
	}
	 
	 function definition_after_data() {
	 	
	 }
	
}
