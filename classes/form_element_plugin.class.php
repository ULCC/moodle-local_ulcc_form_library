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
 * An abstract class that holds methods and attributes common to all element form plugin
 * classes.
 *
 * @abstract
 *
 * @copyright &copy; 2012 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package form
 * @version 1.0
 */

class form_element_plugin {

    /**
     * table to store the properties of the element
     */
    public $tablename;

    /**
     * table to store user data submitted from an element of this type
     * (dd types will also have an intermediate table listing their options
     * user input will be stored as a key to the items table)
     */
    public $data_entry_tablename;

    /**
     * The element data
     *
     * @var array
     */
    var $data;

    /**
     * The name of the plugin
     *
     * @var string
     */
    var $name;

    /**
     * The moodle form for editing the plugin data
     *
     * @var moodleform
     */
    var $mform;

    /**
     * The plugins id
     *
     * @var int
     */
    var $formelement_id;

    /**
     * @var int
     */
    public $formfield_id;

    /**
     * The label used by the instance of the plugin
     *
     * @var string
     */
    var $label;

    /**
     * The decription used by the instance of the plugin
     *
     * @var string
     */
    var $description;

    /**
     * @var string the classname of the XMLDB table, which varies between 1.9 and 2.x
     */
    var $xmldb_table;

    var $xmldb_field;

    var $xmldb_key;

    var $dbman;

    var $set_attributes;

    var $req;

    /**
     * @var int 1 or 0 to indicate whether this field is required.
     */
    public $required;

    /*
     * local file for pre-populating particular types
     * filename is classname . '_pre_items.config'
     * eg ilp_element_plugin_category_pre_items.conf
     * in the local plugins directory
     */
    public $local_config_file;

    /**
     * Constructor
     */
    public function __construct() {
        global $CFG, $DB;

        require_once($CFG->dirroot.'/local/ulcc_form_library/db/form_db.class.php');

        $this->dbc = new form_db();

        $this->name = get_class($this);

        // Include the xmldb classes.
        require_once($CFG->libdir.'/ddllib.php');

        $this->dbman = $DB->get_manager();

        // If 2.0 classes are available then use them.
        $this->xmldb_table = class_exists('xmldb_table') ? 'xmldb_table' : 'XMLDBTable';
        $this->xmldb_field = class_exists('xmldb_field') ? 'xmldb_field' : 'XMLDBField';
        $this->xmldb_key = class_exists('xmldb_key') ? 'xmldb_key' : 'XMLDBKey';

    }

    /**
     *
     */
    public function get_name() {
        return $this->name;
    }

    /**
     *
     */
    public function get_tablename() {
        return $this->tablename;
    }


    /**
     * Edit the plugin instance
     *
     * @param $form_id
     * @param $formelement_id
     * @param $formfield_id
     * @param $moodleplugintype
     * @param $moodlepluginname
     * @param $context_id
     * @internal param object $plugin
     */
    public final function edit($form_id, $formelement_id, $formfield_id, $moodleplugintype, $moodlepluginname, $context_id) {
        global $CFG, $USER;

        // Get the form field record.
        $formfield = $this->dbc->get_form_field_data($formfield_id);

        // Include the moodle form library.
        require_once($CFG->libdir.'/formslib.php');

        // Check if this form element is configurable.

        // Include ilp_formslib.
        // Require_once($CFG->dirroot.'/local/ulcc_form_library/  / / ilp_formslib.class.php');.

        // Get the name of the evidence class being edited.
        $classname = get_class($this).'_mform';

        // Include the moodle form for this table.
        include_once("{$CFG->dirroot}/local/ulcc_form_library/plugin/form_elements/{$classname}.php");

        if (!class_exists($classname)) {
            print_error('noeditilpform', 'local_ulcc_form_library', '', get_class($this));
        }

        if (!empty($formfield->id)) {

            $formelement = $this->dbc->get_form_element_plugin($formfield->formelement_id);

            // Get the form element data from the plugin table.
            $form_element = $this->dbc->get_form_element_by_formfield($formelement->tablename, $formfield->id);

            $non_attrib = array('id', 'timemodified', 'timecreated');

            if (!empty($form_element)) {
                foreach ($form_element as $attrib => $value) {
                    if (!in_array($attrib, $non_attrib)) {
                        $formfield->$attrib = $value;
                    }
                }
            }
            $this->return_data($formfield);
        } else {
            // New element - check for config file.
            if (file_exists($this->local_config_file)) {
                $formfield->optionlist = self::itemlist_flatten(parse_ini_file($this->local_config_file));
            }
        }

        // Instantiate the form and load the data.
        $this->mform = new $classname($form_id, $formelement_id, $USER->id, $moodleplugintype, $moodlepluginname, $context_id);

        if ($this->is_configurable()) {
            $this->mform->set_data($formfield);

            // Enter a back url.
            $backurl = $CFG->wwwroot."/local/ulcc_form_library/actions/edit_formfields.php?form_id={$form_id}&moodleplugintype=
            {$moodleplugintype}&moodlepluginname={$moodlepluginname}&context_id={$context_id}";

            // Was the form cancelled?
            if ($this->mform->is_cancelled()) {
                // Send the user back.
                redirect($backurl, get_string('returnformprompt', 'local_ulcc_form_library'), FORM_REDIRECT_DELAY);
            }

            // Was the form submitted?
            // has the form been submitted?
            if ($this->mform->is_submitted()) {
                // Check the validation rules.
                if ($this->mform->is_validated()) {

                    // Get the form data submitted.
                    $formdata = $this->mform->get_data();
                    $formdata->audit_type = $this->audit_type();

                    // Process the data.
                    $success = $this->mform->process_data($formdata);

                    // If saving the data was not successful.
                    if (!$success) {
                        // Print an error message.
                        print_error(get_string("fieldcreationerror", 'local_ulcc_form_library'), 'local_ulcc_form_library');
                    }

                    if ($this->mform->is_submitted()) {
                        // Return the user to the.
                        $return_url = $CFG->wwwroot."/local/ulcc_form_library/actions/edit_formfields.php?form_id={$form_id}
                        &moodleplugintype={$moodleplugintype}&moodlepluginname={$moodlepluginname}&context_id={$context_id}";
                        redirect($return_url, get_string("fieldcreationsuc", 'local_ulcc_form_library'), FORM_REDIRECT_DELAY);
                    }
                }
            }

        } else {
            $data = new stdClass();
            $data->formelement_id = $formelement_id;

            $this->mform->unprocessed_data($data);

            $success = $this->mform->process_data($data);

            if (!$success) {
                // Print an error message.
                print_error(get_string("fieldcreationerror", 'local_ulcc_form_library'), 'local_ulcc_form_library');
            } else {
                // Return the user to the.
                $return_url = $CFG->wwwroot."/local/ulcc_form_library/actions/edit_formfields.php?form_id={$form_id}
                &moodleplugintype={$moodleplugintype}&moodlepluginname={$moodlepluginname}&context_id={$context_id}";
                redirect($return_url, get_string("fieldcreationsuc", 'local_ulcc_form_library'), FORM_REDIRECT_DELAY);
            }
        }
    }

    /**
     * only necessary in listitem types
     * just here for completeness
     */
    public function return_data(&$formfield) {

    }

    /**
     * take an associative array returned from parsing an ini file
     * and return a string formatted for displaying in a text area on a management form
     */
    public static function itemlist_flatten($configarray, $linesep = "\n", $keysep = ":") {
        $outlist = array();
        foreach ($configarray as $key => $value) {
            $outlist[] = "$key$keysep$value";
        }
        return implode($linesep, $outlist);
    }

    /**
     * Delete the form entry
     */
    public final function delete($formfield_id) {
        return false;
    }


    /**
     * Delete a form element
     */
    public function delete_form_element($formfield_id, $tablename, $extraparams = array()) {
        $formfield = $this->dbc->get_form_element_record($tablename, $formfield_id);

        if ($this->dbc->delete_form_element_by_formfield($tablename, $formfield_id, $extraparams)) {
            // TODO: should we delete all entry records linked to this field?
            // yes we should, and it has been implemented in ilp_element_plugin_itemlist::delete_form_element
            // now delete the formfield.
            return $this->dbc->delete_form_field($formfield_id, $extraparams);
        }
        return false;
    }

    /**
     * Installs any new plugins
     */
    public static function install_new_plugins() {
        global $CFG;

        require_once($CFG->dirroot."/local/ulcc_form_library/lib.php");

        // Instantiate the form db.
        $dbc = new form_db();

        // Get all the currently installed evidence resource types.
        $plugins = form_records_to_menu($dbc->get_form_element_plugins(), 'id', 'name');

        $plugins_directory = $CFG->dirroot.'/local/ulcc_form_library/plugin/form_elements';

        // Get the folder contents of the resource plugin directory.
        $files = scandir($plugins_directory);

        foreach ($files as $file) {
            // Look for plugins.
            if (preg_match('/^([a-z_]+)\.php$/i', $file, $matches)) {

                if (!in_array($matches[1], $plugins) && substr($matches[1], -5) != 'mform') {
                    // Include the class.

                    require_once($plugins_directory.'/'.$file);

                    // Instantiate the object.
                    $class = basename($file, ".php");

                    $formelementobj = new $class();

                    // Install the plugin.
                    $formelementobj->install();

                    // Update the resource_types table.
                    $dbc->create_form_element_plugin($formelementobj->get_name(), $formelementobj->get_tablename());
                }
            }
        }
    }


    function get_resource_enabled_instances($resource_name, $course = null) {

        $enabled_courses = array();

        if (!empty($course)) {
            $course_instances = (is_array($course)) ? $course : array($course);
        } else {
            $course_instances = array();
            // Get all courses that the block is attached to.
            $block_course = $this->dbc->get_block_course_ids($course);

            if (!empty($block_course)) {
                foreach ($block_course as $block_c) {
                    array_push($course_instances, $block_c->pageid);
                }
            }
        }

        if (!empty($course_instances)) {
            foreach ($course_instances as $course_id) {
                $instance_config = (array)$this->dbc->get_instance_config($course_id);
                if (isset($instance_config[$resource_name])) {
                    if (!empty($instance_config[$resource_name])) {
                        array_push($enabled_courses, $course_id);
                    }
                }
            }
        }

        return $enabled_courses;
    }


    /**
     * function used to return configuration settings for a plugin
     */
    function config_settings(&$settings) {
        return $settings;
    }

    /**
     * function used to return the language strings for the resource
     */
    function language_strings(&$string) {
        return $string;
    }

    /**
     * function used to update records in the resource
     */
    function update() {
        return true;
    }


    /**
     * make descendents of this function return false on occasions when
     * the element should not be added to a form
     * eg adding a category selector when there is already a
     * category selector in the same form
     */
    public function can_add($form_id) {
        return true;
    }

    /**
     * This function saves the data entered on a entry form to the plugins _entry table
     * the function expects the data object to contain the id of the entry (it should have been
     * created before this function is called) in a param called id.
     */
    public function entry_process_data($formfield_id, $entry_id, $data) {

        // Check to see if a entry record already exists for the formfield in this plugin.

        // Create the fieldname.
        $fieldname = $formfield_id."_field";

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
            $formelemententry->value = $data->$fieldname;
            $formelemententry->parent_id = $formelementrecord->id;
            $result = $this->dbc->create_formelement_entry($this->data_entry_tablename, $formelemententry);
        } else {
            // Update the current record.
            $formelemententry->audit_type = $this->audit_type(); // Send the audit type through for logging purposes.
            $formelemententry->value = $data->$fieldname;
            $result = $this->dbc->update_formelement_entry($this->data_entry_tablename, $formelemententry);
        }

        return (!empty($result)) ? true : false;
    }

    /**
     * places entry data for the form field given into the entryobj given by the user
     *
     * @param int $formfield_id the id of the formfield that the entry is attached to
     * @param int $entry_id the id of the entry
     * @param object $entryobj an object that will add parameters to
     */
    public function entry_data($formfield_id, $entry_id, &$entryobj) {
        // This function will suffice for 90% of formelements who only have one value field (named value) i
        // in the _ent table of the formelement. However if your formelement has more fields you should override
        // the function.

        // Default entry_data.
        $fieldname = $formfield_id."_field";

        $entry = $this->dbc->get_form_element_entry($this->tablename, $entry_id, $formfield_id);
        if (!empty($entry)) {
            $entryobj->$fieldname = html_entity_decode($entry->value, ENT_QUOTES, 'UTF-8');
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
     * @param bool $returnvalue
     */
    public function view_data($formfield_id, $entry_id, &$entryobj, $returnvalue = false) {
        $this->entry_data($formfield_id, $entry_id, $entryobj);
    }

    /**
     * Function that determiones whether the class in question should have its data process in most cases
     * this should be set to true (so the class wil lnot have to implement) however if the formelement class
     * does not process data (e.g free_html class) then the function should be implemented and should return
     * false
     *
     */
    public function is_processable() {
        return true;
    }

    /**
     * Function that determiones whether the class in question should have its data displayed in any view page
     * this should be set to true (so the class willnot have to implement) however if the formelement class
     * is not viewabke (e.g free_html class) then the function should be implemented and should return
     * false
     */
    public function is_viewable() {
        return true;
    }


    /**
     * Function that determines whether the class in question is configurable this should be set to true
     * (so the class willnot have to implement) however if the form element class is not configurable (e.g page_break class)
     * then the function should be implemented and should return false
     */
    public function is_configurable() {
        return true;
    }
}


