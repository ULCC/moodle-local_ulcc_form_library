<?php

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin_itemlist.class.php');

class form_element_plugin_rdo extends form_element_plugin_itemlist {

    public $tablename;
    public $data_entry_tablename;
    public $items_tablename;
    public $selecttype; //always single - it's a radio group

    /**
     * Constructor
     */
    function __construct() {
        $this->tablename = "ulcc_form_plg_rdo";
        $this->data_entry_tablename = "ulcc_form_plg_rdo_ent";
        $this->items_tablename = "ulcc_form_plg_rdo_items";
        $this->selecttype = FORM_OPTIONSINGLE;
        parent::__construct();
    }

    public function audit_type() {
        return get_string('form_element_plugin_rdo_type', 'local_ulcc_form_library');
    }

    /**
     * function used to return the language strings for the plugin
     */
    function language_strings(&$string) {
        $string['form_element_plugin_rdo'] = 'Radio group';
        $string['form_element_plugin_rdo_type'] = 'radio group';
        $string['form_element_plugin_rdo_description'] = 'A radio group';

        return $string;
    }

    /**
     * This function returns the mform elements taht will be added to a form form
     *
     */
    public function entry_form(MoodleQuickForm &$mform) {

        $fieldname = "{$this->formfield_id}_field";

        $optionlist = $this->get_option_list($this->formfield_id);
        $radioarray = array();
        foreach ($optionlist as $key => $value) {
            $radioarray[] = $mform->createElement('radio', $fieldname, '', $value, $key);
        }

        $mform->addGroup(
            $radioarray,
            $fieldname,
            $this->label,
            '',
            '',
            array('class' => 'form_input'),
            false
        );

        if (!empty($this->required)) {
            $mform->addRule($fieldname, null, 'required', null, 'client');
        }

        $mform->setType($fieldname, PARAM_RAW);
    }

    /**
     * places entry data for the form field given into the entryobj given by the user
     *
     * @param int $formfield_id the id of the formfield that the entry is attached to
     * @param int $entry_id the id of the entry
     * @param object $entryobj an object that will add parameters to
     */
    public function entry_data($formfield_id, $entry_id, &$entryobj) {
        //this function will suffice for 90% of plugins who only have one value field (named value) i
        //in the _ent table of the plugin. However if your plugin has more fields you should override
        //the function

        //default entry_data
        $fieldname = $formfield_id."_field";

        $entry = $this->dbc->get_pluginentry($this->tablename, $entry_id, $formfield_id, true);
        if (!empty($entry)) {
            $entryobj->$fieldname = html_entity_decode($entry->value);
        }

        //loop through all of the data for this entry in the particular entry
        foreach ($entry as $e) {
            $entryobj->$fieldname = $e->parent_id;
        }
    }
}

