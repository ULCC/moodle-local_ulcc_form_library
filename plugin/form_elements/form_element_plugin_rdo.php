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

global $CFG;

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin_itemlist.class.php');

/**
 * Radio buttons field class.
 */
class form_element_plugin_rdo extends form_element_plugin_itemlist {

    public $tablename;
    public $data_entry_tablename;
    public $items_tablename;

    /**
     * @var int Always single - it's a radio group
     */
    public $selecttype;

    /**
     * Constructor
     */
    public function __construct() {
        $this->tablename = "ulcc_form_plg_rdo";
        $this->data_entry_tablename = "ulcc_form_plg_rdo_ent";
        $this->items_tablename = "ulcc_form_plg_rdo_items";
        $this->selecttype = FORM_OPTIONSINGLE;
        parent::__construct();
    }

    /**
     * @return string
     */
    public function audit_type() {
        return get_string('form_element_plugin_rdo_type', 'local_ulcc_form_library');
    }

    /**
     * function used to return the language strings for the plugin
     */
    public function language_strings(&$string) {
        $string['form_element_plugin_rdo'] = 'Radio group';
        $string['form_element_plugin_rdo_type'] = 'radio group';
        $string['form_element_plugin_rdo_description'] = 'A radio group';

        return $string;
    }

    /**
     * This function returns the mform elements that will be added to a form form
     *
     */
    public function entry_form(MoodleQuickForm &$mform) {

        $fieldname = "{$this->formfield_id}_field";

        $optionlist = $this->get_option_list($this->formfield_id);
        $radioarray = array();
        foreach ($optionlist as $itemid => $name) {
            $radioarray[] = $mform->createElement('radio', $fieldname, '', $name, $itemid);
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
     * Places entry data for the form field given into the entryobj given by the user
     *
     * @param int $formfield_id the id of the formfield that the entry is attached to
     * @param int $entry_id the id of the entry
     * @param object $entryobj an object that will add parameters to
     */
    public function entry_data($formfield_id, $entry_id, &$entryobj) {
        // This function will suffice for 90% of plugins who only have one value field (named value) i
        // in the _ent table of the plugin. However if your plugin has more fields you should override
        // the function.

        // Default entry_data.
        $fieldname = $formfield_id."_field";

        $entry = $this->dbc->get_pluginentry($this->tablename, $entry_id, $formfield_id, true);
        if (!empty($entry)) {
            $entryobj->$fieldname = html_entity_decode(reset($entry)->value);
        }
    }
}

