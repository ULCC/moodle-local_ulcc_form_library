<?php

/**
 * itemlists are dropdowns and radio/checkbox groups
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package form library
 * @version 1.0
 */

require_once('form_element_plugin.class.php');

class form_element_plugin_itemlist extends form_element_plugin {

    public function __construct() {
        global $CFG;

        parent::__construct();

        $local_config_filename = get_class($this).'_pre_items.conf';
        $this->local_config_file = $CFG->dirroot.'/local/ulcc_form_library/plugins/form_elements/'.$local_config_filename;

        $this->external_items_table = false;
        $this->external_items_keyfield = 'id';
    }

    /**
     * this function saves the data entered on a entry form to the plugins _entry table
     * the function expects the data object to contain the id of the entry (it should have been
     * created before this function is called) in a param called id.
     * as this is a select element, possibly a multi-select, we have to allow
     * for the possibility that the input is an array of strings
     */
    public function entry_process_data($formfield_id, $entry_id, $data) {

        $result = true;

        // Create the fieldname.
        $fieldname = $formfield_id."_field";

        // Get the plugin table record that has the formfield_id.
        $pluginrecord = $this->dbc->get_plugin_record($this->tablename, $formfield_id);
        if (empty($pluginrecord)) {
            print_error('pluginrecordnotfound');
        }

        // Check to see if a entry record already exists for the formfield in this plugin.
        $multiple = !empty($this->items_tablename);
        $entrydata = $this->dbc->get_pluginentry($this->tablename, $entry_id, $formfield_id, $multiple);

        // If there are records connected to this entry in this formfield_id.
        if (!empty($entrydata)) {
            // Delete all of the entries.
            $extraparams = array('audit_type' => $this->audit_type());
            foreach ($entrydata as $e) {
                $this->dbc->delete_element_record_by_id($this->data_entry_tablename, $e->id, $extraparams);
            }
        }

        // Create new entries.
        $pluginentry = new stdClass();
        $pluginentry->audit_type = $this->audit_type();
        $pluginentry->entry_id = $entry_id;
        $pluginentry->value = (!empty($data->$fieldname)) ? $data->$fieldname : '';
        // Pass the values given to $entryvalues as an array.
        $entryvalues = (!is_array($pluginentry->value)) ? array($pluginentry->value) : $pluginentry->value;

        foreach ($entryvalues as $ev) {
            if (!empty($ev)) {
                $state_item =
                    $this->dbc->get_state_item_id($this->tablename, $pluginrecord->id, $ev, $this->external_items_keyfield,
                                                  $this->external_items_table);
                $pluginentry->parent_id = $state_item->parent_id;
                $pluginentry->value = $state_item->value;
                $result = $this->dbc->create_plugin_entry($this->data_entry_tablename, $pluginentry);
            }
        }

        return $result;
    }

    /**
     * places entry data for the form field given into the entryobj given by the user
     *
     * @param int $formfield_id the id of the formfield that the entry is attached to
     * @param int $entry_id the id of the entry
     * @param object $entryobj an object that will add parameters to
     */
    public function entry_data($formfield_id, $entry_id, &$entryobj) {
        //this function will suffix for 90% of plugins who only have one value field (named value) i
        //in the _ent table of the plugin. However if your plugin has more fields you should override
        //the function

        //default entry_data
        $fieldname = $formfield_id."_field";

        $entry = $this->dbc->get_pluginentry($this->tablename, $entry_id, $formfield_id, true);

        if (!empty($entry)) {
            $fielddata = array();

            //loop through all of the data for this entry in the particular entry
            foreach ($entry as $e) {
                $fielddata[] = $e->parent_id;
            }

            //save the data to the objects field
            $entryobj->$fieldname = $fielddata;
        }
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
     * @param bool returnvalue should a label or value be returned
     */
    public function view_data($formfield_id, $entry_id, &$entryobj, $returnvalue = false) {
        $fieldname = $formfield_id."_field";
        $entry = $this->dbc->get_pluginentry($this->tablename, $entry_id, $formfield_id, true);
        if (!empty($returnvalue)) {
            $entryobj->$fieldname = array();
        }
        if (!empty($entry)) {
            $fielddata = array();
            $comma = "";
            //loop through all of the data for this entry in the particular entry
            foreach ($entry as $e) {
                if (empty($returnvalue)) {
                    $entryobj->$fieldname .= "{$comma}{$e->name}";
                    $comma = ",";
                } else {
                    array_push($entryobj->$fieldname, $e->value);
                }
            }
        }
    }

    public function load($formfield_id) {
        $formfield = $this->dbc->get_form_field_data($formfield_id);
        if (!empty($formfield)) {
            $this->formfield_id = $formfield_id;
            $this->formelement_id = $formfield->formelement_id;
            $plugin = $this->dbc->get_form_element_plugin($formfield->formelement_id);
            $pluginrecord = $this->dbc->get_form_element_by_formfield($this->tablename, $formfield->id);
            if (!empty($pluginrecord)) {
                $this->id = $pluginrecord->id;
                $this->label = $formfield->label;
                $this->description = $formfield->description;
                $this->required = $formfield->required;
                $this->position = $formfield->position;
            }
        }
        return false;
    }

    /*
     * get the list options with which to populate the edit element for this list element
     */
    public function return_data(&$formfield) {
        $data_exists = $this->dbc->plugin_data_item_exists($this->tablename, $formfield->id);
        if (empty($data_exists)) {
            //if no, get options list
            $formfield->optionlist = $this->get_option_list_text($formfield->id);
        } else {
            $formfield->existing_options = $this->get_option_list_text($formfield->id, '<br />');
        }
    }

    /*
    * get options from the items table for this plugin, and concatenate them into a string
    * @param int $formfield_id
    * @param string $sep
    * @param string $field - optional additional field to retrieve, along with value and name
    */
    protected function get_option_list_text($formfield_id, $sep = "\n", $field = false) {
        $optionlist = $this->get_option_list($formfield_id, $field, false);
        $rtn = '';

        if (!empty($optionlist)) {
            foreach ($optionlist as $key => $value) {
                $rtn .= "$key:$value$sep";
            }
        }
        return $rtn;
    }

    /**
     * read rows from item table and return them as array of key=>value
     * @param int $formfield_id
     * @param bool|string $field - extra field to read from items table: used by form_element_plugin_state
     * @param bool    $useid    should ids be returned as the value or should the actual value
     *
     * @return array
     */
    protected function get_option_list($formfield_id, $field = false, $useid = true) {
        $outlist = array();
        if ($formfield_id) {
            // TODO $field is pointless here.
            $objlist = $this->dbc->get_optionlist($formfield_id, $this->tablename, $field);

            foreach ($objlist as $obj) {
                //obj->value will only be returned if specifically requested (this should only before value editing)
                //in all other cases id should be returned
                $value = (!empty($useid)) ? $obj->id : $obj->value;
                $outlist[$value] = $obj->name;
            }
        }
        return $outlist;
    }

    /**
     * converts a string of options into an array
     * @param string $optstring the string that you want to convert
     * @param string $optsep the seperator that is used to seperate lines
     *
     * @return array array given list converted into an array
     */
    public static function optlist2Array($optstring, $optsep = "\n") {

        $keysep = ":";
        $optlist = explode($optsep, $optstring);
        //now split each entry into key and value
        $outlist = array();
        foreach ($optlist as $row) {
            if ($row) {
                $row = explode($keysep, $row);
                $key = trim($row[0]);
                if (1 == count($row)) {
                    $value = trim($row[0]);
                } elseif (2 < count($row)) {
                    $value = array(
                        trim($row[1]),
                        trim($row[2])
                    );
                }
                elseif (1 < count($row)) {
                    $value = trim($row[1]);
                }
                $outlist[$key] = $value;
            }
        }
        return $outlist;
    }

    /**
     * this function returns the mform elements that will be added to a form form
     *
     * @param MoodleQuickForm $mform
     */
    public function entry_form(MoodleQuickForm &$mform) {

        //create the fieldname
        $fieldname = "{$this->formfield_id}_field";

        //definition for user form
        $optionlist = $this->get_option_list($this->formfield_id);

        if (!empty($this->description)) {
            $mform->addElement('static', "{$fieldname}_desc", $this->label,
                               strip_tags(html_entity_decode($this->description), FORM_STRIP_TAGS_DESCRIPTION));
            $this->label = '';
        }

        //text field for element label
        $select = &$mform->addElement(
            'select',
            $fieldname,
            $this->label,
            $optionlist,
            array('class' => 'form_input')
        );

        if (FORM_OPTIONMULTI == $this->selecttype) {
            $select->setMultiple(true);
        }

        if (!empty($this->req)) {
            $mform->addRule($fieldname, null, 'required', null, 'client');
        }
        $mform->setType('label', PARAM_RAW);
    }

    /**
     * Deletes a form element and any items that it may have
     *
     * @param int $formfield_id the id of the formfield
     */
    public function delete_form_element($formfield_id, $tablename = null, $extraparams = null) {
        //get the record for the field
        $pluginrecord = $this->dbc->get_form_element_by_formfield($this->tablename, $formfield_id);

        if (!empty($this->items_tablename)) {
            //delete all items for the field then delete the field itself by calling the function in the
            //parent class
            $this->dbc->delete_items($this->items_tablename, $pluginrecord->id);
        }

        //also delete any submitted data - it'll survive in ghostly form in the log table
        $this->dbc->delete_items($this->data_entry_tablename, $pluginrecord->id);

        $formfield = $this->dbc->get_form_field_data($formfield_id);

        $extraparams = array(
            'audit_type' => $this->audit_type(),
            'label' => $formfield->label,
            'description' => $formfield->description,
            'id' => $formfield_id
        );
        return parent::delete_form_element($formfield_id, $this->tablename, $extraparams);
    }

    public function uninstall() {
        $table = new $this->xmldb_table($this->tablename);
        drop_table($table);

        $table = new $this->xmldb_table($this->data_entry_tablename);
        drop_table($table);
        if ($this->items_tablename) {
            $table = new $this->xmldb_table($this->items_tablename);
        }
        drop_table($table);
    }

    public function install() {
        global $CFG, $DB;

        // create the table to store form fields
        $table = new $this->xmldb_table($this->tablename);
        $set_attributes = method_exists($this->xmldb_key, 'set_attributes') ? 'set_attributes' : 'setAttributes';

        $table_id = new $this->xmldb_field('id');
        $table_id->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addField($table_id);

        $table_form = new $this->xmldb_field('formfield_id');
        $table_form->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_form);

        $table_optiontype = new $this->xmldb_field('selecttype');
        $table_optiontype->$set_attributes(XMLDB_TYPE_INTEGER, 1, XMLDB_UNSIGNED,
                                           null); //1=single, 2=multi cf blocks/form/constants.php
        $table->addField($table_optiontype);

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
        $table_key->$set_attributes(XMLDB_KEY_FOREIGN_UNIQUE, array('formfield_id'), 'block_form_form_field', 'id');
        $table->addKey($table_key);

        if (!$this->dbman->table_exists($table)) {
            $this->dbman->create_table($table);
        }

        // create the new table to store dropdown options
        if ($this->items_tablename) {
            $table = new $this->xmldb_table($this->items_tablename);

            $table_id = new $this->xmldb_field('id');
            $table_id->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $table->addField($table_id);

            $table_textfieldid = new $this->xmldb_field('parent_id');
            $table_textfieldid->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
            $table->addField($table_textfieldid);

            $table_itemvalue = new $this->xmldb_field('value');
            $table_itemvalue->$set_attributes(XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
            $table->addField($table_itemvalue);

            $table_itemname = new $this->xmldb_field('name');
            $table_itemname->$set_attributes(XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
            $table->addField($table_itemname);

            $table_timemodified = new $this->xmldb_field('timemodified');
            $table_timemodified->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
            $table->addField($table_timemodified);

            $table_timecreated = new $this->xmldb_field('timecreated');
            $table_timecreated->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
            $table->addField($table_timecreated);

            $table_key = new $this->xmldb_key('primary');
            $table_key->$set_attributes(XMLDB_KEY_PRIMARY, array('id'));
            $table->addKey($table_key);

            $table_key = new $this->xmldb_key('listplugin_unique_fk');
            $table_key->$set_attributes(XMLDB_KEY_FOREIGN, array('parent_id'), $this->tablename, 'id');
            $table->addKey($table_key);

            /*
               $table_key = new $this->xmldb_key('textplugin_unique_entry');
               $table_key->$set_attributes(XMLDB_KEY_FOREIGN, array('entry_id'),'block_form_entry','id');
               $table->addKey($table_key);
       */

            if (!$this->dbman->table_exists($table)) {
                $this->dbman->create_table($table);
            }
        }

        // create the new table to store responses to fields
        $table = new $this->xmldb_table($this->data_entry_tablename);

        $table_id = new $this->xmldb_field('id');
        $table_id->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addField($table_id);

        $table_maxlength = new $this->xmldb_field('parent_id');
        $table_maxlength->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_maxlength);

        $table_item_id = new $this->xmldb_field('value'); // Foreign key -> $this->items_tablename.
        $table_item_id->$set_attributes(XMLDB_TYPE_CHAR, 255, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_item_id);

        $table_form = new $this->xmldb_field('entry_id');
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

        $table_key = new $this->xmldb_key('listpluginentry_unique_fk');
        $table_key->$set_attributes(XMLDB_KEY_FOREIGN, array('parent_id'), $this->tablename, 'id');
        $table->addKey($table_key);

        /*
                $table_key = new $this->xmldb_key('textplugin_unique_entry');
                $table_key->$set_attributes(XMLDB_KEY_FOREIGN, array('entry_id'),'block_form_entry','id');
                $table->addKey($table_key);
        */

        if (!$this->dbman->table_exists($table)) {
            $this->dbman->create_table($table);
        }
    }
}