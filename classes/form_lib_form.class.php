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
 * Wrapper class for Moodleform, this class adds additional functions to aid in the creation and usage
 * of multi page forms.
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package forms lib
 * @version 1.0
 *
 *
 */

global $CFG;

require_once($CFG->libdir.'/formslib.php');

require_once($CFG->dirroot.'/local/ulcc_form_library/db/form_db.class.php');

/**
 * Abstract class that really only has one implementation, so should really be merged with form_entry_mform
 */
abstract class form_lib_form extends moodleform {

    /**
     * @var int
     */
    public $form_id;

    /**
     * @var form_db
     */
    public $dbc;

    /**
     * @var stdClass
     */
    public $formdata;

    /**
     * @var int
     */
    protected $currentpage;

    /**
     * Returns data submitted from previous pages on the current form.
     * (this feature is not available using the normal get_data and get_submitted data functions)
     *
     * @param int $form_id the id of the mutlipage form that we want to get submitted data for
     *
     * @return mixed stdClass or null if not data is found
     */
    public function get_multipage_data($form_id) {

        $normdata = $this->get_submitted_data();

        $formfields = $this->dbc->get_form_fields_by_position($form_id);

        if (!empty($formfields)) {

            $elementnames = array();
            $data = array();

            foreach ($formfields as $ff) {
                $elementnames[] = $ff->id."_field";
            }

            $elementnames[] = 'previousbutton';
            $elementnames[] = 'nextbutton';

            $submiteddata = array_merge($_GET, $_POST);

            foreach ($submiteddata as $key => $sd) {
                foreach ($elementnames as $en) {

                    // We will find anything with a name beginning with the code name of a field
                    // e.g 9_field 9_field_test will both be found and returned.
                    if (preg_match("/\b{$en}/i", $key)) {
                        if (is_array($sd) && count($sd) > 3) {
                            if (array_key_exists('day', $sd) && array_key_exists('month', $sd) && array_key_exists('year', $sd)) {
                                $sd = make_timestamp($sd['year'],
                                    $sd['month'],
                                    $sd['day'],
                                    0, 0, 0, 99, true);
                            }
                        }
                        $data[$key] = $sd;
                    }
                }
            }

            $normdata = (is_array($normdata)) ? $normdata : (array)$normdata;

            return (object)array_merge($normdata, $data);
        }

        return null;
    }

    /**
     * Find out if the next button was pressed and act on it if necessary.
     *
     * @return bool
     */
    public function next() {

        $this->formdata = (empty($this->formdata)) ? $this->get_multipage_data($this->form_id) : $this->formdata;

        // Was the next button pressed?
        if (isset($this->formdata->nextbutton)) {

            $this->save_current_page_data();

            return true;
        }

        return false;
    }

    /**
     * Saves the data from the current page. Used if the next or previous button has been pressed.
     */
    protected function save_current_page_data() {

        global $SESSION;

        $cformdata = $this->formdata;

        // We do not want any of the following data to be saved as it stop the pagination features from working.
        if (isset($cformdata->current_page)) {
            unset($cformdata->current_page);
        }
        if (isset($cformdata->previousbutton)) {
            unset($cformdata->previousbutton);
        }
        if (isset($cformdata->nextbutton)) {
            unset($cformdata->nextbutton);
        }

        // Save all data submitted from last page.

        // Check if the page data array has been created in the session.
        if (!isset($SESSION->pagedata)) {
            $SESSION->pagedata = array();
        }

        // Create an array to hold the page temp_data.
        if (!isset($SESSION->pagedata[$this->form_id])) {
            $SESSION->pagedata[$this->form_id] = array();
        }

        if (!isset($SESSION->pagedata[$this->form_id][$this->currentpage])) {
            // If no data has been saved for the current page save the data to the dd
            // and save the key.
            $SESSION->pagedata[$this->form_id][$this->currentpage] = $this->dbc->save_temp_data($cformdata);
        } else {
            // If data for this page has already been saved get the key and update the record.
            $tempid = $SESSION->pagedata[$this->form_id][$this->currentpage];
            $this->dbc->update_temp_data($tempid, $cformdata);
        }
    }

    /**
     * Carries out operations necessary if the form is a multipage form and the previous button has been pressed
     *
     * @return bool
     */
    public function previous() {

        $this->formdata = (empty($this->formdata)) ? $this->get_multipage_data($this->form_id) : $this->formdata;

        if (isset($this->formdata->previousbutton)) {

            $this->save_current_page_data();

            return true;
        }

        return false;
    }

    /**
     * Submits all data from all form pages to the database.
     *
     * @return int id of the form entry record
     */
    public function submit() {

        global $SESSION;

        $form_id = $this->form_id;

        // Get all of the submitted data.
        $this->formdata = $this->get_multipage_data($form_id);
        $darray = array();

        // The session contains a list of ids in the temp data table.
        if (!empty($SESSION->pagedata[$form_id])) {
            foreach ($SESSION->pagedata[$form_id] as $tempid) {
                $tempdata = $this->dbc->get_temp_data($tempid);
                $tempdata = (is_array($tempdata)) ? $tempdata : (array)$tempdata;
                $darray = array_merge($darray, $tempdata);
                $this->dbc->delete_temp_data($tempid);
            }

            // Important in case someone else changes the data, then the user goes back to the form - it'll
            // reload their old stuff from the session.
            unset($SESSION->pagedata[$form_id]);
        }

        $formdata = (is_array($this->formdata)) ? $this->formdata : (array)$this->formdata;

        $formdata = array_merge($formdata, $darray);

        return $this->process_data($formdata);
    }

    /**
     * @param int|bool $entry_id
     */
    public function load_entry($entry_id = false) {

        global $CFG, $SESSION;

        if (!empty($entry_id)) {

            // Create a entry_data object this will hold the data that will be passed to the form.
            $entry_data = new stdClass();

            // Get the main entry record.
            $entry = $this->dbc->get_form_entry($entry_id);

            if (!empty($entry)) {
                // Check if the maximum edit field has been set for this report.

                // Get all of the fields in the current report, they will be returned in order as.
                // no position has been specified.
                $formfields = $this->dbc->get_form_fields_by_position($entry->form_id);

                foreach ($formfields as $field) {

                    // Get the plugin record that for the plugin.
                    $pluginrecord = $this->dbc->get_form_element_plugin($field->formelement_id);

                    // Take the name field from the plugin as it will be used to call the instantiate the plugin class.
                    $classname = $pluginrecord->name;

                    // Include the class for the plugin.
                    include_once("{$CFG->dirroot}/local/ulcc_form_library/plugin/form_elements/{$classname}.php");

                    if (!class_exists($classname)) {
                        print_error('noclassforplugin', 'local_ulcc_form_library', '', $pluginrecord->name);
                    }

                    /* @var form_element_plugin $pluginclass */
                    $pluginclass = new $classname();

                    $pluginclass->load($field->id);

                    // Create the fieldname.
                    $fieldname = $field->id."_field";

                    $pluginclass->load($field->id);

                    // Call the plugin class entry data method.
                    $pluginclass->entry_data($field->id, $entry_id, $entry_data);
                }

                // Set the data in the form.
                $this->set_data($entry_data);
            }
        }

        // If we have data from previous pages (next/previous buttons used on multi page form), load it here.
        // Set the data in the page to what it equalled before.
        if (isset($SESSION->pagedata[$this->form_id][$this->currentpage])) {
            $tempdata = $this->dbc->get_temp_data($SESSION->pagedata[$this->form_id][$this->currentpage]);
            $this->set_data($tempdata);
        }
    }

    /**
     * @param bool $entry_id
     * @param bool $labels
     * @param array $dontreturn
     * @return bool|stdClass
     */
    public function return_entry($entry_id = false, $labels = false, $dontreturn = array()) {

        global $CFG;

        if (!empty($entry_id)) {

            // Create a entry_data object this will hold the data that will be passed to the form.
            $entry_data = new stdClass();

            // Get the main entry record.
            $entry = $this->dbc->get_form_entry($entry_id);

            $entrydata = array();

            if (!empty($entry)) {
                // Check if the maximum edit field has been set for this report.

                // Get all of the fields in the current report, they will be returned in order as
                // no position has been specified.
                $formfields = $this->dbc->get_form_fields_by_position($entry->form_id);

                foreach ($formfields as $field) {

                    // Get the plugin record that for the plugin.
                    $pluginrecord = $this->dbc->get_form_element_plugin($field->formelement_id);
                    if (!in_array($pluginrecord->name, $dontreturn)) {
                        // Take the name field from the plugin as it will be used to call the instantiate the plugin class.
                        $classname = $pluginrecord->name;

                        // Include the class for the plugin.
                        include_once("{$CFG->dirroot}/local/ulcc_form_library/plugin/form_elements/{$classname}.php");

                        if (!class_exists($classname)) {
                            print_error('noclassforplugin', 'local_ulcc_form_library', '', $pluginrecord->name);
                        }

                        /* @var form_element_plugin $pluginclass */
                        $pluginclass = new $classname();

                        // Create the fieldname.
                        $fieldname = $field->id."_field";

                        if ($pluginclass->is_viewable() != false) {
                            $pluginclass->load($field->id);

                            // Call the plugin class entry data method.
                            $pluginclass->view_data($field->id, $entry->id, $entry_data);

                            if (!empty($labels)) {
                                if (!empty($entry_data->$fieldname)) {
                                    $fielddata = $entry_data->$fieldname;
                                } else {
                                    $fielddata = '';
                                }
                                $entry_data->$fieldname = array('label' => $field->label,
                                                                'value' => $fielddata);
                            }
                        } else {
                            $dontdisplay[] = $field->id;
                        }
                    }
                }

                return $entry_data;
            }
        }

        return false;
    }

    /**
     * This tells us whether the form is in use by a plugin and therefore should not be editable.
     *
     * @param $pluginname
     * @return bool
     */
    public function is_in_use($pluginname) {
        global $DB;

        switch ($pluginname) {
            case 'coursework':

                if ($DB->record_exists('coursework', array('formid' => $this->form_id))) {
                    return true;
                }
                break;

            default:
                return false;

        }
    }

    /**
     * @abstract
     * @param $data
     * @return mixed
     */
    abstract protected function process_data($data);
}
