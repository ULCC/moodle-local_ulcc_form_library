<?php

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin_itemlist_mform.class.php');
require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin_itemlist.class.php');


class form_element_plugin_state_mform  extends form_element_plugin_itemlist_mform {

	public $tablename;
	public $items_tablename;

	function __construct($form_id,$formelement_id,$creator_id,$moodleplugintype,$moodlepluginname,$context_id,$formfield_id=null) {
		parent::__construct($form_id,$formelement_id,$creator_id,$moodleplugintype,$moodlepluginname,$context_id,$formfield_id);
		$this->tablename = "ulcc_form_plg_ste";
		$this->items_tablename = "ulcc_form_plg_ste_items";
	}

    /*
    * the admin has entered the states in the fail and pass textareas on the mform
    * the values in those textareas have been made into arrays and sent to this function, to be categorised as fail, pass or unset
    * @param array $statelist - list of values - should be a key and value from the state selector, so that if either of them matches, we can return a pass or fail value
    * @param array $fail_list - list of values to be classified as fail
    * @param array $pass_list - list of values to be classified as pass
    * @param array $unset_list - not really necessary ... if nothing matches, we default to unset anyway
    */
    protected function deduceItemState( $state_list, $fail_list, $pass_list,$notcounted_list, $keysep=':' ){

        foreach( $state_list as $grade ){
	        $grade = trim( $grade );

            if( in_array( $grade, $fail_list ) ){
	            return FORM_STATE_FAIL;
	        }

            if( in_array( $grade, $pass_list ) ){
                return FORM_STATE_PASS;
            }

            if( in_array( $grade, $notcounted_list ) ){
                return FORM_STATE_NOTCOUNTED;
            }
        }
        return FORM_STATE_UNSET;
    }

		/**
		textarea element to contain the options the admin wishes to add to the user form
		admin will be instructed to insert value/label pairs in the following plaintext format:
		value1:label1\nvalue2:label2\nvalue3:label3
		or some such
		*/
	protected function specific_definition(MoodleQuickForm $mform) {

		$mform->addElement(
			'textarea',
			'optionlist',
			get_string( 'form_element_plugin_dd_optionlist', 'local_ulcc_form_library' ),
			array('class' => 'form_input')
	    );

        $mform->addElement(
            'static',
            'existing_options',
            get_string( 'form_element_plugin_dd_existing_options' , 'local_ulcc_form_library' ),
            ''
        );

		$mform->addElement(
			'textarea',
			'fail',
			get_string( 'form_element_plugin_state_fail', 'local_ulcc_form_library' ),
			array('class' => 'form_input')
	    );

		$mform->addElement(
			'textarea',
			'pass',
			get_string( 'form_element_plugin_state_pass', 'local_ulcc_form_library' ),
			array('class' => 'form_input')
	    );

        $mform->addElement(
            'textarea',
            'notcounted',
            get_string( 'form_element_plugin_state_notcounted', 'local_ulcc_form_library' ),
            array('class' => 'form_input')
        );

        $mform->addElement(
            'hidden',
            'existing_options_hidden'
        );

		//admin must specify at least 1 option, with at least 1 character
        $mform->addRule('optionlist', null, 'minlength', 1, 'client');

	}


    protected function is_valid_item( $item, $item_list, $keysep=":" ){
        $item = trim( $item );
        $itemparts = explode( $keysep, $item );

        foreach( $itemparts  as $item ) {
            //$item should be either a key or value of $item_list
            if( in_array( $item, array_values( $item_list ) ) || in_array( $item, array_keys( $item_list ) ) ){
                return true;
            }
        }
        return false;
    }

	protected function specific_validation($data) {

        $optionlist = (isset($data[ 'optionlist' ])) ? form_element_plugin_itemlist::optlist2Array( $data[ 'optionlist' ] ) : array();

        //this code is based on the current rule that any option that exits can not be removed
        //if this changes this code will need to be changed
        $existingoptionlist = (isset($data[ 'existing_options_hidden' ])) ? form_element_plugin_itemlist::optlist2Array( $data[ 'existing_options_hidden' ] ,"<br />" ) : array();
        $optionlist =   array_merge($optionlist,$existingoptionlist);

        //all contents of $data->fail and $data->pass must match valid keys or values in $optionlist
        $sep = "\n";
        $keysep = ":";
        $fail_item_list         = explode( $sep, $data[ 'fail' ] );
        $pass_item_list         = explode( $sep, $data[ 'pass' ] );
        $notcounted_item_list   = explode( $sep, $data[ 'notcounted' ] );



        foreach( array( $fail_item_list, $pass_item_list, $notcounted_item_list ) as $item_list ){
            foreach( $item_list as $submitted_item ){
                if( trim( $submitted_item ) && !$this->is_valid_item( $submitted_item , $optionlist, $keysep ) ){
                    $this->errors[] = get_string( 'form_element_plugin_error_not_valid_item' , 'local_ulcc_form_library' ) . ": <em>$submitted_item</em>";
                }
            }
        }

    }


	/*
	* take input from the management form and write the element info
	*/
	 protected function specific_process_data($data) {

         $optionlist = (isset($data->optionlist)) ? form_element_plugin_itemlist::optlist2Array( $data->optionlist ) : array();

        $sep = "\n";
        $keysep = ":";
		//entries from data to go into $this->tablename and $this->items_tablename

        $gradekeylist = array(
             'pass', 'fail','notcounted'
        );

       foreach( $gradekeylist as $key ){
         $v = $key . '_list';
         $$v = explode( $sep, $data->$key );
         //deal with pesky whitespace
         foreach( $$v as &$entry ){
             $entry = trim( $entry );
             $entryparts = explode( $keysep , $entry );
             if( 1 < count( $entryparts ) ){
                 //admin has copied a whole key:value string into the pass or fail textarea
                 //so throw away the key
                 $entry = $entryparts[1];
             }
         }
        }

        //we now have 2 lists: $pass_list and $fail_list

         $plgrec = (!empty($data->formfield_id)) ? $this->dbc->get_form_element_record($this->tablename,$data->formfield_id) : false;

	 	if (empty($plgrec)) {
			//options for this dropdown need to be written to the items table
			//each option is one row
	 		$element_id = $this->dbc->create_form_element_record($this->tablename,$data);

			//$itemrecord is a container for item data
			$itemrecord = new stdClass();
			$itemrecord->parent_id = $element_id;
			foreach( $optionlist as $key=>$itemname ){
				//one item row inserted here
				$itemrecord->value = $key;
				$itemrecord->name = $itemname;
                $itemrecord->passfail = $this->deduceItemState( array( $itemname, $key ), $fail_list, $pass_list, $notcounted_list );
	 			$this->dbc->create_form_element_record($this->items_tablename,$itemrecord);
			}
	 	} else {
	 		//get the old record from the elements plugins table
	 		$oldrecord				=	$this->dbc->get_form_element_by_formfield($this->tablename,$data->formfield_id);
			$data_exists = $this->dbc->plugin_data_item_exists( $this->tablename, $data->formfield_id );
			$element_id = $this->dbc->get_element_id_from_formfield_id( $this->tablename, $data->formfield_id );
			//$itemrecord is a container for item data
			$itemrecord = new stdClass();
			$itemrecord->parent_id = $element_id;

			if( empty( $data_exists ) ){
				//no user data - go ahead and delete existing items for this element, to be replaced by the submitted ones in $data
				$delstatus = $this->dbc->delete_element_listitems( $this->tablename, $data->formfield_id );
					//if $delstatus false, there has been an error - alert the user
			} else {
				//user data has been submitted already - don't delete existing items, but add new ones if they are in $data
				//purge $optionlist of already existing item_keys
				//then it will be safe to write the items to the items table
				foreach( $optionlist as $key=>$itemname ){
					if( $this->dbc->listelement_item_exists( $this->items_tablename, array( 'parent_id' => $element_id, 'value' => $key ) ) ){
						//this should never happen, because it shouldn't have passed validation, but you never know
						unset( $optionlist[ $key ] );
						//alert the user
					}
				}
			}
			//now write fresh options from $data
			foreach( $optionlist as $key=>$itemname ){
				//one item row inserted here
				$itemrecord->value = $key;
				$itemrecord->name = $itemname;
                $itemrecord->passfail = $this->deduceItemState( array( $itemname, $key ), $fail_list, $pass_list, $notcounted_list );
		 		$this->dbc->create_form_element_record($this->items_tablename,$itemrecord);
			}

	 		//create a new object to hold the updated data
	 		$pluginrecord 			=	new stdClass();
	 		$pluginrecord->id		=	$oldrecord->id;
	 		$pluginrecord->optionlist	=	$data->optionlist;
			$pluginrecord->selecttype 	= 	FORM_OPTIONSINGLE;

	 		//update the plugin with the new data
	 		//return $this->dbc->update_plugin_record($this->tablename,$pluginrecord);
	 	}
	 }
}
