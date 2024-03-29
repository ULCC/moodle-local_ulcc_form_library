<?php

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin_itemlist_mform.class.php');

class form_element_plugin_status_mform  extends form_element_plugin_itemlist_mform {

	public 	$tablename;
	public 	$items_tablename;
	public	$formfield_link_table;



	function __construct($form_id,$formelement_id,$creator_id,$moodleplugintype,$moodlepluginname,$context_id,$formfield_id=null) {
		parent::__construct($form_id,$formelement_id,$creator_id,$moodleplugintype,$moodlepluginname,$context_id,$formfield_id);
		$this->tablename = "ulcc_form_plg_sts";
		$this->items_tablename = "ulcc_form_plg_sts_items";
		$this->formfield_link_table	=	'ulcc_form_plg_rf_sts';
	}


	function specific_validation($data) {

	}


	function specific_definition(MoodleQuickForm $mform) {

		//the id of the statusfield, we should only be using the default status field
        $mform->addElement('hidden', 'status_id');
        $mform->setType('status_id', PARAM_INT);

        //THE status id should be the first status item id 1
        $mform->setDefault('status_id', '1');
	}


	function specific_process_data($data) {
		global 	$USER;

			$statusfield						=	new stdClass();
			$statusfield->selecttype			=	FORM_OPTIONSINGLE;
			$statusfield->formfield_id			=	$data->formfield_id;
			$statusfield->creator_id			=	$USER->id;

			$this->dbc->create_statusfield($statusfield);

	}


	//for settings configuration only

    /*
    * the admin has entered the states in the unset, fail and pass textareas on the mform
    * the values in those textareas have been made into arrays and sent to this function, to be categorised as fail, pass or unset
    * @param array $statelist - list of values - should be a key and value from the state selector, so that if either of them matches, we can return a pass or fail value
    * @param array $fail_list - list of values to be classified as fail
    * @param array $pass_list - list of values to be classified as pass
    * @param array $unset_list - not really necessary ... if nothing matches, we default to unset anyway
    */
    /* todo: REMOVE THIS function as it does not look like it is being used by anything
    protected function deducePassFailFromLists( $state_list, $fail_list, $pass_list, $keysep=':' )	{
        foreach( $state_list as $grade ){
	        $grade = trim( $grade );
	        if( in_array( $grade, $fail_list ) ){
	            return FORM_STATE_FAIL;
	        }
	        if( in_array( $grade, $pass_list ) ){
	            return FORM_STATE_PASS;
	        }
        }
        return FORM_STATE_UNSET;
    }
	*/

    protected function is_valid_item( $item, $item_list, $keysep=":" ){
        $item = trim( $item );
        $itemparts = explode( $keysep, $item );
        foreach( $itemparts  as $item ){
            //$item should be either a key or value of $item_list
            if( in_array( $item, array_values( $item_list ) ) || in_array( $item, array_keys( $item_list ) ) ){
                return true;
            }
        }
        return false;
    }



}
