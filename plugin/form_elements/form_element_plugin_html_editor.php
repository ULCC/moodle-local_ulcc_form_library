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

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin.class.php');

/**
 * Plugin that adds a normal editor.
 */
class form_element_plugin_html_editor extends form_element_plugin {

    public $tablename;
    public $data_entry_tablename;
    public $minimumlength; // Defined by the form creator to validate user input.
    public $maximumlength; // Defined by the form creator to validate user input.

    /**
     * Constructor
     */
    public function __construct() {
        $this->tablename = "ulcc_form_plg_hte";
        $this->data_entry_tablename = "ulcc_form_plg_hte_ent";

        parent::__construct();
    }

    /**
     * TODO comment this
     *
     */
    public function load($formfield_id) {
        $formfield = $this->dbc->get_form_field_data($formfield_id);
        if (!empty($formfield)) {
            // Set the formfield_id var.
            $this->formfield_id = $formfield_id;

            // Get the record of the plugin used for the field.
            $plugin = $this->dbc->get_form_element_plugin($formfield->formelement_id);

            $this->formelement_id = $formfield->formelement_id;

            // Get the form element record for the formfield.
            $pluginrecord = $this->dbc->get_form_element_by_formfield($this->tablename, $formfield->id);

            if (!empty($pluginrecord)) {
                $this->label = $formfield->label;
                $this->description = $formfield->description;
                $this->required = $formfield->required;
                $this->maximumlength = $pluginrecord->maximumlength;
                $this->minimumlength = $pluginrecord->minimumlength;
                $this->position = $formfield->position;
                return true;
            }
        }
        return false;
    }

    /**
     *
     */
    public function install() {

        // Create the table to store form fields.
        $table = new $this->xmldb_table($this->tablename);
        $set_attributes = method_exists($this->xmldb_key, 'set_attributes') ? 'set_attributes' : 'setAttributes';

        $table_id = new $this->xmldb_field('id');
        $table_id->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addField($table_id);

        $table_form = new $this->xmldb_field('formfield_id');
        $table_form->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_form);

        $table_minlength = new $this->xmldb_field('minimumlength');
        $table_minlength->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_minlength);

        $table_maxlength = new $this->xmldb_field('maximumlength');
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

        $table_key = new $this->xmldb_key('htmleditorplugin_unique_formfield');
        $table_key->$set_attributes(XMLDB_KEY_FOREIGN_UNIQUE, array('formfield_id'), 'ulcc_form_lib_form_field', 'id');
        $table->addKey($table_key);

        if (!$this->dbman->table_exists($table)) {
            $this->dbman->create_table($table);
        }

        // Create the new table to store responses to fields.
        $table = new $this->xmldb_table($this->data_entry_tablename);
        $set_attributes = method_exists($this->xmldb_key, 'set_attributes') ? 'set_attributes' : 'setAttributes';

        $table_id = new $this->xmldb_field('id');
        $table_id->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addField($table_id);

        $table_title = new $this->xmldb_field('value');
        $table_title->$set_attributes(XMLDB_TYPE_TEXT);
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
        $table_key->$set_attributes(XMLDB_KEY_FOREIGN, array('parent_id'), $this->tablename, 'id');
        $table->addKey($table_key);

        if (!$this->dbman->table_exists($table)) {
            $this->dbman->create_table($table);
        }
    }

    /**
     *
     */
    public function uninstall() {
        $table = new $this->xmldb_table($this->tablename);
        drop_table($table);

        $table = new $this->xmldb_table($this->data_entry_tablename);
        drop_table($table);
    }

    /**
     *
     */
    public function audit_type() {
        return get_string('form_element_plugin_html_editor_type', 'local_ulcc_form_library');
    }

    /**
     * function used to return the language strings for the plugin
     */
    public function language_strings(&$string) {
        $string['form_element_plugin_html_editor'] = 'Htmleditor';
        $string['form_element_plugin_html_editor_type'] = 'Html editor';
        $string['form_element_plugin_html_editor_description'] = 'A html editor';
        $string['form_element_plugin_html_editor_minimumlength'] = 'Minimum Length';
        $string['form_element_plugin_html_editor_maximumlength'] = 'Maximum Length';
        $string['form_element_plugin_html_editor_maxlengthrange'] = 'The maximum length field must have a value between 0 and 255';
        $string['form_element_plugin_html_editor_maxlessthanmin'] =
            'The maximum length field must have a greater value than the minimum length';

        return $string;
    }

    /**
     * Delete a form element
     */
    public function delete_form_element($formfield_id, $tablename = null, $extraparams = null) {
        return parent::delete_form_element($formfield_id, $this->tablename);
    }

    /**
     * this function returns the mform elements taht will be added to a form form
     *
     * @param MoodleQuickForm $mform
     */
    public function entry_form(&$mform) {

        // Create the fieldname.
        $fieldname = "{$this->formfield_id}_field";

        if (!empty($this->description)) {
            $mform->addElement('static', "{$fieldname}_desc", $this->label,
                               strip_tags(html_entity_decode($this->description), FORM_STRIP_TAGS_DESCRIPTION));
            $this->label = '';
        }
        // Text field for element label.
        $mform->addElement(
            'editor',
            $fieldname,
            "$this->label",
            array('class' => 'form_input',
                  'canUseHtmlEditor' => 'detect',
                  'rows' => '20',
                  'cols' => '65')
        );

        // Disabled until http://tracker.moodle.org/browse/MDL-35402 is fixed as multiple rules is breaking it.
        //if (!empty($this->minimumlength)) $mform->addRule($fieldname, null, 'minlength', $this->minimumlength, 'client');
        //if (!empty($this->maximumlength)) $mform->addRule($fieldname, null, 'maxlength', $this->maximumlength, 'client');
        if (!empty($this->required)) {
            $mform->addRule($fieldname, null, 'required', null, 'client');
        }
        $mform->setType($fieldname, PARAM_RAW);
    }

    /**
     * handle user input
     **/
    public function entry_specific_process_data($formfield_id, $entry_id, $data) {

        // Check to see if a entry record already exists for the formfield in this plugin.

        // Create the fieldname.
        $fieldname = $formfield_id."_field";
        $editor = $data->$fieldname;
        $inputtext = $editor['text'];

        // Get the plugin table record that has the formfield_id.
        $formelementrecord = $this->dbc->get_form_element_record($this->tablename, $formfield_id);
        if (empty($formelementrecord)) {
            print_error('formelementrecordnotfound');
        }

        // Get the _entry table record that has the formelementrecord id.
        $formelemententry = $this->dbc->get_form_element_entry($this->tablename, $entry_id, $formfield_id);

        // If no record has been created create the entry record.
        if (empty($formelemententry)) {
            $formelemententry = new stdClass();
            $formelemententry->audit_type = $this->audit_type(); // Send the audit type through for logging purposes.
            $formelemententry->entry_id = $entry_id;
            $formelemententry->value = $inputtext;
            $formelemententry->parent_id = $formelementrecord->id;
            $result = $this->dbc->create_formelement_entry($this->data_entry_tablename, $formelemententry);
        } else {
            // Update the current record.
            $formelemententry->audit_type = $this->audit_type(); // Send the audit type through for logging purposes.
            $formelemententry->value = $inputtext;
            $result = $this->dbc->update_formelement_entry($this->data_entry_tablename, $formelemententry);
        }

        return (!empty($result)) ? true : false;
    }

    /**
     * We need a different array key for this element.
     *
     * @param int $fieldid
     * @param int $entry_id
     * @param object $entry_data
     */
    public function entry_data($fieldid, $entry_id, &$entry_data) {
        parent::entry_data($fieldid, $entry_id, $entry_data);

        $fieldname = $fieldid."_field";

        if (empty($entry_data->$fieldname)) {
            return;
        }

        $newfieldnametext = $fieldname."['text']";
        $newfieldnameformat = $fieldname."['format']";

        $data = $entry_data->$fieldname;
        $entry_data->$fieldname = array(
            'text' => $data,
            'format' => 1
        );

    }
}

