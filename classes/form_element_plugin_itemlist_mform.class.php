<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Provides a form in which the configuration of a itemlist type element can be entered
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package form library
 * @version 1.0
 */

global $CFG;

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin_mform.class.php');

class form_element_plugin_itemlist_mform extends form_element_plugin_mform {

    public $tablename;
    public $items_tablename;

    function __construct($form_id, $formelement_id, $creator_id, $moodleplugintype, $moodlepluginname, $context_id,
                         $formfield_id = null) {
        parent::__construct($form_id, $formelement_id, $creator_id, $moodleplugintype, $moodlepluginname, $context_id,
            $formfield_id);
        // Remember to define $this->tablename and $this->items_tablename in the child class.
    }


    protected function specific_validation($data) {
        $data = (object)$data;
        $optionlist = array();
        if (in_array('optionlist', array_keys((array)$data))) {
            // DD type needs to take values from admin form and write them to items table.
            $optionlist = form_element_plugin_itemlist::optlist2Array($data->optionlist);
        }
        $element_id = $this->dbc->get_element_id_from_formfield_id($this->tablename, $data->formfield_id);
        $plgrec = (!empty($data->formfield_id)) ? $this->dbc->get_form_element_record($this->tablename,
            $data->formfield_id) : false;
        if (empty($plgrec)) {
            // New element.
        } else {
            // Existing element - check for user data.
            $data_exists = $this->dbc->form_element_data_item_exists($this->tablename, $data->formfield_id);
            if (empty($data_exists)) {
                // No problem.
            } else {
                // Check for keys in $optionlist which clash with already existing keys in the element items.
                foreach ($optionlist as $key => $itemname) {
                    if ($this->dbc->listelement_item_exists($this->items_tablename, array('parent_id' => $element_id,
                        'value' => $key))) {
                        $this->errors[] = get_string('form_element_plugin_error_item_key_exists', 'block_form').": $key";
                    }
                }
            }
        }
        // Check for duplicate keys in $optionlist.
        $usedkeys = array();
        foreach ($optionlist as $key => $itemname) {
            if (in_array($key, $usedkeys)) {
                $this->errors[] = get_string('form_element_plugin_error_duplicate_key', 'block_form').": $key";
            } else {
                $usedkeys[] = $key;
            }
        }
        return $this->errors;
    }

    protected function specific_definition(MoodleQuickForm $mform) {

        /*
        textarea element to contain the options the admin wishes to add to the user form
        admin will be instructed to insert value/label pairs in the following plaintext format:
        value1:label1\nvalue2:label2\nvalue3:label3
        or some such
        */

        $mform->addElement(
            'textarea',
            'optionlist',
            get_string('form_element_plugin_dd_optionlist', 'block_form'),
            array('class' => 'form_input')
        );

        // Admin must specify at least 1 option, with at least 1 character.
        $mform->addRule('optionlist', null, 'minlength', 1, 'client');

    }

    /*
     * take input from the management form and write the element info
     */
    protected function specific_process_data($data) {
        $optionlist = array();
        if (in_array('optionlist', array_keys((array)$data))) {
            // ...dd type needs to take values from admin form and writen them to items table.
            $optionlist = form_element_plugin_itemlist::optlist2Array($data->optionlist);
        }
        // Entries from data to go into $this->tablename and $this->items_tablename.

        $plgrec = (!empty($data->formfield_id)) ? $this->dbc->get_form_element_record($this->tablename,
            $data->formfield_id) : false;

        if (empty($plgrec)) {
            // Options for this dropdown need to be written to the items table
            // each option is one row.
            $element_id = $this->dbc->create_form_element_record($this->tablename, $data);

            // ...$itemrecord is a container for item data.
            $itemrecord = new stdClass();
            $itemrecord->parent_id = $element_id;
            foreach ($optionlist as $key => $itemname) {
                // One item row inserted here.
                $itemrecord->value = $key;
                $itemrecord->name = $itemname;
                $this->dbc->create_form_element_record($this->items_tablename, $itemrecord);
            }
        } else {
            // Get the old record from the elements plugins table.
            $oldrecord = $this->dbc->get_form_element_by_formfield($this->tablename, $data->formfield_id);
            $data_exists = $this->dbc->form_element_data_item_exists($this->tablename, $data->formfield_id);
            $element_id = $this->dbc->get_element_id_from_formfield_id($this->tablename, $data->formfield_id);
            // ...$itemrecord is a container for item data.
            $itemrecord = new stdClass();
            $itemrecord->parent_id = $element_id;

            if (empty($data_exists)) {
                // No user data - go ahead and delete existing items for this element, to be replaced by the submitted ones
                // in $data.
                $delstatus = $this->dbc->delete_element_listitems($this->tablename, $data->formfield_id);
                // If $delstatus false, there has been an error - alert the user.
            } else {
                // User data has been submitted already - don't delete existing items, but add new ones if they are in $data
                // purge $optionlist of already existing item_keys
                // then it will be safe to write the items to the items table.
                foreach ($optionlist as $key => $itemname) {
                    if ($this->dbc->listelement_item_exists($this->items_tablename, array('parent_id' => $element_id,
                        'value' => $key))) {
                        // This should never happen, because it shouldn't have passed validation, but you never know.
                        unset($optionlist[$key]);
                        // Alert the user.
                    }
                }
            }
            // Now write fresh options from $data.
            foreach ($optionlist as $key => $itemname) {
                // One item row inserted here.
                $itemrecord->value = $key;
                $itemrecord->name = $itemname;
                $this->dbc->create_form_element_record($this->items_tablename, $itemrecord);
            }

            // Create a new object to hold the updated data.
            $pluginrecord = new stdClass();
            $pluginrecord->id = $oldrecord->id;
            $pluginrecord->optionlist = $data->optionlist;
            $pluginrecord->selecttype = FORM_OPTIONSINGLE;

            // Update the plugin with the new data.
            // return $this->dbc->update_plugin_record($this->tablename,$pluginrecord);.
        }
    }
}


