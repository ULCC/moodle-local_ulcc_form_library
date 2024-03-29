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
 * Provides a interface that can be used to access form data and display forms
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package Form Library
 * @version 1.0
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

// Require the form db class.
require_once($CFG->dirroot.'/local/ulcc_form_library/db/form_db.class.php');

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/forms/form_entry_mform.php');

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_parser.class.php');

/**
 * Main class for the custom form.
 */
class ulcc_form {

    /**
     * @var
     */
    private $plugintype;

    /**
     * @var
     */
    private $pluginname;

    /**
     * @var form_db
     */
    private $dbc;

    /**
     * @var null
     */
    private $formdata;

    /**
     * @var int id from the forms table
     */
    private $formid;

    /**
     * @var int what sequential page of a multipage form are we on?
     */
    private $currentpage;

    /**
     * @var string the url of the page we are on.
     * @todo Possibly not needed as we could use $PAGE->url?
     */
    private $pageurl;

    /**
     * @var string Where to redirect to if the form is cancelled.
     */
    private $cancelurl;

    /**
     * @var form_entry_mform
     */
    private $mform;

    /**
     * @var int is of the entry record if there is one.
     */
    private $entryid;

    /**
     * @var bool We have to make sure that an attempt has been made to save page data before displaying.
     */
    private $formpagessaved = false;

    /**
     * @param string $plugintype
     * @param string $pluginname
     * @param int $formid
     * @param string $pageurl
     * @param string $cancelurl
     * @param int $entryid
     * @param int $currentpage
     */
    public function __construct($plugintype, $pluginname, $formid= 0, $entryid = 0, $pageurl = '',
                                $cancelurl = '', $currentpage = 1) {

        $this->plugintype = $plugintype;
        $this->pluginname = $pluginname;
        $this->formid = $formid;
        $this->currentpage = $currentpage;
        $this->pageurl = $pageurl;
        $this->cancelurl = $cancelurl;
        $this->entryid = $entryid;
        $this->dbc = new form_db();
        $this->formdata = null;
    }

    /**
     * Returns an array contain all forms that have been created for the current plugin
     *
     * @param  string $formtype filter on form type
     *
     * @param bool $disabled
     * @return array of objects or false
     */
    public function get_plugin_forms($formtype = null, $disabled = false) {
        return $this->dbc->get_plugin_forms($this->pluginname, $this->plugintype, $formtype, $disabled);
    }

    /**
     * @throws coding_exception
     * @return int|bool entry id if it was submitted, false otherwise.
     */
    public function display_form() {

        if (!$this->formpagessaved) {
            throw new coding_exception('Must run try_to_save_whole_form_and_get_entry_id() before displaying the form');
        }

        $mform = $this->get_mform();

        // Set the current page variable inside of the form.

        // Check if the form has already been submitted if not display the form.
        if ($mform->is_cancelled()) {
            // Send the user back to dashboard.
            redirect($this->cancelurl, '', FORM_REDIRECT_DELAY);
        }

        // Loads the data into the form.
        $mform->load_entry($this->entryid);

        $mform->display();

    }

    /**
     * Gets the mform so stuff can be done with it. Does sanity checks. Will rebuild the form based on the current
     * page.
     *
     * @return form_entry_mform
     * @throws coding_exception
     */
    private function get_mform() {

        global $SESSION;

        if (empty($this->formid)) {
            throw new coding_exception('No form id specified. Cannot display form');
        }

        // Check if the form is part of the current plugin.
        if (!$this->dbc->is_plugin_form($this->pluginname, $this->plugintype, $this->formid)) {
            throw new coding_exception('Trying to display a form that does not belong to this plugin');
        }

        $formrecord = $this->dbc->get_form_by_id($this->formid);

        if (empty($formrecord->status)) {
            throw new coding_exception('Form definition is not finished yet. Cannot display until the form is enabled.');
        }
        if (!empty($formrecord->deleted)) {
            throw new coding_exception('Form has been deleted. Cannot display.');
        }

        // Unset the current page variable otherwise moodleform will take it and use it in the
        // in the current form (which will overwrite any changes we make to the current page element).
        unset($_POST['current_page']);

        $page_data = optional_param('page_data', 0, PARAM_RAW);

        // The page_data element is part of all forms if it is not found and there is a session var for this form
        // then it must be for all data unset it.
        if (empty($page_data) && isset($SESSION->pagedata[$this->formid])) {
            unset($SESSION->pagedata[$this->formid]);
        }

        $mform = new form_entry_mform($this->formid, $this->plugintype, $this->pluginname, $this->pageurl,
                                      $this->entryid, $this->currentpage);
        $this->mform = $mform;

        return $mform;
    }

    /**
     * This will save form data if it's there. This will process the move to the next or previous page, so if it
     * returns false, then we know that although a page may have changed, the form has not been submitted in
     * its entirety, so still needs displaying.
     *
     * @return int|bool
     */
    public function try_to_save_whole_form_and_get_entry_id() {

        global $SESSION;

        if ($this->formpagessaved) {
            return false;
        }

        $mform = $this->get_mform();

        $this->formpagessaved = true;

        // Was the form submitted?
        // Has the form been submitted? This might mean we need to go to the next page, or it might mean ending.
        if ($mform->is_submitted()) { // Has any data at all in GET or POST.

            // Shift forwards/backwards by a page if we need to. We need to get a new version of the form once this
            // has happened so that the fields can be rebuilt for the next/previous page. For this reason, the functions
            // here save the temp form data in the DB (and place the id of the record in the session), then we
            // keep track of the page we are on here so when display() is called, we get a new form with the new
            // elements.
            if ($mform->next()) {
                $this->currentpage++;
            } else if ($mform->previous()) {
                $this->currentpage--;
            }

            // Get the form data submitted.
            $formdata = $mform->get_multipage_data($this->formid);
            $this->formdata = $formdata;

            if (isset($formdata->submitbutton)) { // Submit and finish button, rather than next or previous.

                // Contains process_data.
                $entryid = $mform->submit();

                // We no longer need the form information for this page.
                unset($SESSION->pagedata[$this->formid]);

                // If saving the data was not successful.
                if (!$entryid) {
                    // Print an error message. Probably an exception before this happens.
                    print_error(get_string("entrycreationerror", 'block_ilp'), 'block_ilp');
                    return false;
                }

                return $entryid; // Means a successful save (exception if not).
            }
        }

        return false; // Means we still need to display the form.
    }

    /**
     * returns the data for the specified entry
     *
     * @param $entry_id
     * @return bool|\stdClass
     */
    public function get_form_entry($entry_id) {

        $entrydata = false;

        // Get the main entry record.
        $entry = $this->dbc->get_form_entry($entry_id);

        if (!empty($entry)) {
            $mform = new form_entry_mform($entry->form_id, false, false, false);
            $entrydata = $mform->return_entry($entry_id);
        }

        return (!empty($entrydata)) ? $entrydata : false;
    }

    /**
     * @param int $entry_id
     * @param array $removeelement
     * @return string
     */
    public function display_form_entry($entry_id, $removeelement = array()) {
        global $CFG;

        $entrydata = false;

        // Get the main entry record.
        $entry = $this->dbc->get_form_entry($entry_id);

        $formentry = get_string('entrynotfound', 'local_ulcc_form_library');

        if (!empty($entry)) {
            $mform = new form_entry_mform($entry->form_id, false, false, false);
            $entrydata = $mform->return_entry($entry_id, true, $removeelement);

            if (!empty($entrydata)) {
                ob_start();
                // Must not be include once, or else we'll only get one entry!
                include($CFG->dirroot."/local/ulcc_form_library/views/entry_display.html");

                $formentry = ob_get_contents();

                ob_end_clean();
            }
        }

        return $formentry;
    }

    /**
     * @param string $fieldname
     * @return mixed
     */
    public function get_form_field_value($fieldname) {
        if (!empty($this->formdata) && isset($this->formdata->$fieldname)) {
            return $this->formdata->$fieldname;
        }
        return null;
    }

    /**
     * Returns the value of the form element specified
     *
     * @param int    $entry_id      the id of the entry whose value will be returned
     * @param string $elementtype   the name of the element that will be returned
     * @param bool   $rawvalue      should the raw value be returned or should the value be passed through
     *                              the form elements view function
     * @return array|bool
     */
    public function get_form_element_value($entry_id, $elementtype, $rawvalue) {
        global $CFG;

        $entry = $this->dbc->get_form_entry($entry_id);
        $formelement = $this->dbc->get_form_element_by_name($elementtype);

        if (!empty($entry) && !empty($formelement)) {
            if ($formfields = $this->dbc->element_occurances($entry->form_id, $formelement->tablename)) {

                $formdata = new stdClass();

                // Take the name field from the plugin as it will be used to call the instantiate the plugin class.
                $classname = $formelement->name;

                // Instantiate the form element class.
                /* @var form_element_plugin_itemlist $formelementclass */
                $formelementclass = new $classname();

                // Include the class for the plugin.
                include_once("{$CFG->dirroot}/local/ulcc_form_library/plugin/form_elements/{$classname}.php");

                foreach ($formfields as $ff) {

                    $formelementclass->load($ff->id);

                    // Call the plugin class entry data method.
                    if (empty($rawvalue)) {
                        $formelementclass->view_data($ff->id, $entry_id, $formdata);
                    } else {
                        $formelementclass->entry_data($ff->id, $entry_id, $formdata);
                    }
                }

                $fielddata = array();

                foreach ($formdata as $field) {
                    $fielddata[] = $field;
                }

                return $fielddata;
            }
        }

        return false;
    }

    /**
     * Returns true or false based on whether the form with the id given has a element of the type specified
     *
     * @param int       $form_id   the id of the form that we will check for the element
     * @param string    $elementtype the element type that will be looked for.
     * @return bool
     */
    public function has_element_type($form_id, $elementtype) {
        $formelement = $this->dbc->get_form_element_by_name($elementtype);
        $formfields = $this->dbc->element_occurances($form_id, $formelement->tablename);
        return (!empty($formfields)) ? true : false;
    }

    /**
     * @param $form_id
     * @param $creator_id
     * @return mixed
     */
    public function create_form_entry($form_id, $creator_id) {
        global $CFG;

        $entry = new stdClass();
        $entry->form_id = $form_id;
        $entry->creator_id = $creator_id;

        $entry_id = $this->dbc->create_entry($entry);

        // Get all of the fields in the current report, they will be returned in order as
        // no position has been specified.
        $formfields = $this->dbc->get_form_fields_by_position($form_id);

        $data = new stdClass();

        foreach ($formfields as $field) {

            // Get the plugin record that for the plugin.
            $formelementrecord = $this->dbc->get_form_element_plugin($field->formelement_id);

            // Take the name field from the plugin as it will be used to call the instantiate the plugin class.
            $classname = $formelementrecord->name;

            // Include the class for the plugin.
            include_once("{$CFG->dirroot}/local/ulcc_form_library/plugin/form_elements/{$classname}.php");

            if (!class_exists($classname)) {
                print_error('noclassforplugin', 'block_ilp', '', $formelementrecord->name);
            }

            // Instantiate the plugin class.
            /* @var form_element_plugin_itemlist $pluginclass */
            $pluginclass = new $classname();

            $pluginclass->load($field->id);

            $formfield = $field->id.'_field';

            if (get_parent_class($pluginclass) != 'form_element_plugin_itemlist') {
                $data->$formfield = "";
            } else {
                // Get items for this instance of the form element.
                $items = $this->dbc->get_optionlist($field->id, $formelementrecord->tablename);
                if (!empty($items)) {
                    $item = array_pop($items);
                    $data->$formfield = $item->id;
                }
            }

            // Call the plugins entry_form function which will add an instance of the plugin
            // to the form.
            if ($pluginclass->is_processable()) {
                if (!$pluginclass->entry_process_data($field->id, $entry_id, $data)) {
                    $result = false;
                }
            }
        }
        return $entry_id;
    }

    /**
     * Sets the value of a particular form element within the given entry
     *
     * @param $entry_id     int     the id of the entry whose value will be set
     * @param $elementtype  string  the name of the element that will be set
     * @param $value        mixed   the value to be set
     * @param int $occurance int    the occurance to set e.g 1 = first 2 = 2nd etc
     * @return bool
     */
    public function set_form_element_entry_value($entry_id, $elementtype, $value, $occurance = 1) {

        global $CFG;

        $entry = $this->dbc->get_form_entry($entry_id);
        $formelement = $this->dbc->get_form_element_by_name($elementtype);

        if (!empty($entry) && !empty($formelement)) {
            if ($formfields = $this->dbc->element_occurances($entry->form_id, $formelement->tablename)) {

                $formdata = new stdClass();

                // Take the name field from the plugin as it will be used to call the instantiate the plugin class.
                $classname = $formelement->name;

                // Instantiate the form element class.
                /* @var form_element_plugin_itemlist $formelementclass */
                $formelementclass = new $classname();

                // Include the class for the plugin.
                include_once("{$CFG->dirroot}/local/ulcc_form_library/plugin/form_elements/{$classname}.php");

                $i = 0;
                $data = new stdClass();

                if (!empty($formfields)) {
                    foreach ($formfields as $ff) {

                        $formelementclass->load($ff->id);

                        $formfield = $ff->id.'_field';

                        $data->$formfield = $value;

                        // Call the plugins entry_form function which will add an instance of the plugin
                        // to the form.
                        if ($formelementclass->is_processable() && $i == $occurance - 1) {
                            if (!$formelementclass->entry_process_data($ff->id, $entry_id, $data)) {
                                $result = false;
                            }
                        }

                        $i++;
                    }

                    return true;
                }
            }
        }

        return false;
    }
}
