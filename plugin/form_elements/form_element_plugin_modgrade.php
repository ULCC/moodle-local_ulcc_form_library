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

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin.class.php');

/**
 * Plugin that allows a grade to be collected that's specific to this module. This is so it can be put into the gradebook.
 */
class form_element_plugin_modgrade extends form_element_plugin {

    /**
     * @var string
     */
    public $tablename;

    /**
     * @var string
     */
    public $data_entry_tablename;

    /**
     * @var
     */
    protected $gradetype;

    /**
     * @var
     */
    protected $gradetablename;

    /**
     * @var
     */
    protected $gradescale;

    /**
     * Constructor
     */
    public function __construct() {

        $this->tablename = "ulcc_form_plg_modgd";
        $this->data_entry_tablename = "ulcc_form_plg_modgd_ent";

        parent::__construct();
    }

    /**
     * TODO comment this
     * called when user form is submitted
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
                $this->gradetablename = $pluginrecord->tablename;
                $this->gradetype = $pluginrecord->gradetype;
                $this->gradescale = $pluginrecord->gradescale;
                $this->position = $formfield->position;
                $this->audit_type = $this->audit_type();
                return true;
            }
        }
        return false;
    }

    /**
     * create tables for this plugin
     */
    public function install() {

        // Create the table to store form fields.
        /* @var xmldb_table $table */
        $table = new $this->xmldb_table($this->tablename);
        $set_attributes = method_exists($this->xmldb_key, 'set_attributes') ? 'set_attributes' : 'setAttributes';

        $table_id = new $this->xmldb_field('id');
        $table_id->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addField($table_id);

        $table_form = new $this->xmldb_field('formfield_id');
        $table_form->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_form);

        $table_form = new $this->xmldb_field('gradetype');
        $table_form->$set_attributes(XMLDB_TYPE_INTEGER, 1, XMLDB_UNSIGNED, null);
        $table->addField($table_form);

        $table_form = new $this->xmldb_field('gradescale');
        $table_form->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, null);
        $table->addField($table_form);

        $table_form = new $this->xmldb_field('tablename');
        $table_form->$set_attributes(XMLDB_TYPE_CHAR, 255, null);
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

        $table_key = new $this->xmldb_key('modgrade_unique_formfield');
        $table_key->$set_attributes(XMLDB_KEY_FOREIGN_UNIQUE, array('formfield_id'), 'local_ulcc_form_library_form_field', 'id');
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
        $table_key->$set_attributes(XMLDB_KEY_FOREIGN, array('parent_id'), $this->tablename, 'id');
        $table->addKey($table_key);

        if (!$this->dbman->table_exists($table)) {
            $this->dbman->create_table($table);
        }
    }

    /**
     * Removes tables etc.
     */
    public function uninstall() {

        global $DB;

        $manager = $DB->get_manager();

        $table = new $this->xmldb_table($this->tablename);
        $manager->drop_table($table);

        $table = new $this->xmldb_table($this->data_entry_tablename);
        $manager->drop_table($table);
    }

    /**
     *
     */
    public function audit_type() {
        return get_string('form_element_plugin_modgrade_type', 'local_ulcc_form_library');
    }

    /**
     * function used to return the language strings for the plugin
     */
    public function language_strings(&$string) {
        $string['form_element_plugin_modgrade'] = 'Module grade selector';
        $string['form_element_plugin_modgrade_type'] = 'Module grade selector';
        $string['form_element_plugin_modgrade_description'] = 'A module grade selector';
        $string['form_element_plugin_modgrade_dynamicdesc'] = 'The dynamic checkbox below defines whether the grade
        produced in Module grade selector will be chosen now or will be chosen at run time using the data taken from the given
        database. If you choose to make the selector dynamic then choose the module that you are working with.';
        $string['form_element_plugin_modgrade_gradetype'] = 'Dynamic grade selector';
        $string['form_element_plugin_modgrade_module'] = 'Module: ';
        $string['form_element_plugin_modgrade_gradescale'] = 'Grade scale';

        return $string;
    }

    /**
     * Delete a form element
     */
    public function delete_form_element($formfield_id, $tablename = null, $extraparams = null) {
        $formfield = $this->dbc->get_form_field_data($formfield_id);
        $extraparams = array(
            'audit_type' => $this->audit_type(),
            'label' => $formfield->label,
            'description' => $formfield->description,
            'id' => $formfield_id
        );
        return parent::delete_form_element($formfield_id, $this->tablename, $extraparams);
    }

    /**
     * This function returns the mform elements that will be added to a form form
     *
     * @param MoodleQuickForm $mform
     * @throws coding_exception
     * @return void
     */
    public function entry_form(MoodleQuickForm &$mform) {

        global $DB, $PAGE;

        $fieldname = "{$this->formfield_id}_field";
        if (!empty($this->description)) {
            $mform->addElement('static', "{$fieldname}_desc", $this->label,
                               strip_tags(html_entity_decode($this->description), FORM_STRIP_TAGS_DESCRIPTION));
            $this->label = '';
        }

        // Must be a coursemodule!
        if ($PAGE->context->contextlevel != CONTEXT_MODULE) {
            // Disabled as it prevents preview from working.
            // TODO Need to put this back in somewhere else.
            // throw new coding_exception('Trying to use a form with a modgrade element in a non-module context');
        }

        // Get the module table record. We assume that this element has only been allowed in a module context.
        if($coursemodule = $PAGE->cm){

        $modulename = $DB->get_field('modules', 'name', array('id' => $coursemodule->module));

        // Different modules have different names for the grade field.
        switch ($modulename) {

            case 'coursework':
                $gradefield = 'grade';
                break;

            default:
                $gradefield = 'grade';
        }

        $grade = $DB->get_field($modulename, $gradefield, array('id' => $coursemodule->instance));

        // Might be a scale...
        if ($grade < 0) {
            $scale = $DB->get_record('scale', array('id' => $grade));
            $grademenu = make_menu_from_list($scale->scale);
        } else {
            // If a record with a grade has been found then populate gradesmenu with this.
            $grademenu = make_grades_menu($grade);
        }

        $grademenu['-1'] = get_string('nograde');

        $mform->addElement('select',
                           $fieldname,
                           "$this->label",
                           $grademenu);
        }

        if (!empty($this->required)) {
            $mform->addRule($fieldname, null, 'required', null, 'client');
        }
    }

    /**
     * handle user input
     **/
    public function entry_specific_process_data($formfield_id, $entry_id, $data) {
        /*
        * parent method is fine for simple form element types
        * dd types will need something more elaborate to handle the intermediate
        * items table and foreign key
        */
        return $this->entry_process_data($formfield_id, $entry_id, $data);
    }

    /**
     * Determines whether a user can add a new instance of the mod grade plugin to the form
     *
     * @param int $form_id the id of the form that will be checked to see if it has the element
     * @return bool
     */
    public function can_add($form_id) {
        return !$this->dbc->element_type_exists($form_id, $this->tablename);
    }
}
