<?php

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin_itemlist.class.php');

class form_element_plugin_checkbox extends form_element_plugin_itemlist {

    public $tablename;  //table to hold fields structure
    public $data_entry_tablename; //table to hold actual values
    public $items_tablename; //name for options
    public $selecttype; // to allow checking several boxes within a group

    /**
     * Constructor
     */
    function __construct() {
        $this->tablename = "ulcc_form_plg_chb";
        $this->data_entry_tablename = "ulcc_form_plg_chb_ent";
        $this->items_tablename = "ulcc_form_plg_chb_items";
        $this->selecttype = FORM_OPTIONMULTI;
        parent::__construct();
    }




    public function audit_type() {
        return get_string('form_element_plugin_checkbox_type','local_ulcc_form_library');
    }


    /**
     * function used to return the language strings for the plugin
     */
    function language_strings(&$string) {
        $string['form_element_plugin_checkbox'] 		= 'Check boxes ';
        $string['form_element_plugin_checkbox_type'] 		= 'check boxes';
        $string['form_element_plugin_checkbox_description'] 	= 'A checkboxes group';
        $string['form_element_plugin_checkbox_optionlist'] = 'Option list';
        $string['form_element_plugin_checkbox_existing_options'] = 'Existing options';

        return $string;
    }


    /**
     * this function returns the mform elements that will be added to a  form
     *
     */
    public	function entry_form(MoodleQuickForm &$mform ) {

        $fieldname	=	"{$this->formfield_id}_field";

        $optionlist = $this->get_option_list( $this->formfield_id );
        $chbarray = array();
        //checkboxes
        foreach( $optionlist as $key => $value ){
            $chbarray[] = $mform->createElement( 'checkbox', $fieldname."[$key]", '', $value);
        }

        $mform->addGroup(
            $chbarray,
            $fieldname,
            $this->label,
            '',
            '',
            array('class' => 'form_input'),
            false
        );

        if (!empty($this->req)) $mform->addRule($fieldname, null, 'required', null, 'client');

        $mform->setType($fieldname, PARAM_RAW);
    }


    public	function entry_process_data($formfield_id,$entry_id,$data) {

        $result	=	true;

        //create the fieldname
        $fieldname =	$formfield_id."_field";

        //get the plugin table record that has the formfield_id
        $pluginrecord	=	$this->dbc->get_plugin_record($this->tablename,$formfield_id);
        if (empty($pluginrecord)) {
            print_error('pluginrecordnotfound');
        }

        //check to see if a entry record already exists for the reportfield in this plugin
        $multiple = !empty( $this->items_tablename );
        $entrydata 	=	$this->dbc->get_pluginentry($this->tablename, $entry_id,$formfield_id,$multiple);

        //if there are records connected to this entry in this formfield_id
        if (!empty($entrydata)) {
            //delete all of the entries
            $extraparams = array( 'audit_type' => $this->audit_type() );
            foreach ($entrydata as $e)	{
                $this->dbc->delete_element_record_by_id( $this->data_entry_tablename, $e->id, $extraparams );
            }
        }

        //create new entries
        $pluginentry			=	new stdClass();
        $pluginentry->audit_type = $this->audit_type();
        $pluginentry->entry_id  = 	$entry_id;
        $pluginentry->value		=	( !empty( $data->$fieldname ) ) ? $data->$fieldname : '' ;
        //pass the values given to $entryvalues as an array
        $entryvalues	=	(!is_array($pluginentry->value)) ? array($pluginentry->value): $pluginentry->value;

        foreach ($entryvalues as $ev=>$value) {
            if( !empty( $ev ) ){
                $state_item				=	$this->dbc->get_state_item_id($this->tablename,$pluginrecord->id,$ev, $this->external_items_keyfield, $this->external_items_table );
                $pluginentry->parent_id	=	$state_item->id;
                $pluginentry->value 	= 	$state_item->value;
                $result					= 	$this->dbc->create_plugin_entry($this->data_entry_tablename,$pluginentry);
            }
        }

        return	$result;
    }


    /**
     * places entry data for the report field given into the entryobj given by the user
     *
     * @param int $formfield_id the id of the reportfield that the entry is attached to
     * @param int $entry_id the id of the entry
     * @param object $entryobj an object that will add parameters to
     */
    public function entry_data( $formfield_id,$entry_id,&$entryobj ){
        //this function will suffice for 90% of plugins who only have one value field (named value) i
        //in the _ent table of the plugin. However if your plugin has more fields you should override
        //the function

        //default entry_data
        $fieldname	=	$formfield_id."_field";

        $entry	=	$this->dbc->get_pluginentry($this->tablename,$entry_id,$formfield_id,true);

        if (!empty($entry)) {

            $fielddata	=	array();

            //loop through all of the data for this entry in the particular entry
            foreach($entry as $e) {
                $fielddata[$e->parent_id]	=	$e->parent_id;
            }

            //save the data to the objects field
            $entryobj->$fieldname	=	$fielddata;
        }

    }

}












