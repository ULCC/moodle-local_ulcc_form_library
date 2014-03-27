<?php

/**
 * Creates a entry form
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package Form Library
 * @version 1.0
 */

global $CFG;

require_once($CFG->libdir . '/formslib.php');

// Include the db class.
require_once($CFG->dirroot . '/local/ulcc_form_library/db/form_db.class.php');

require_once($CFG->dirroot . '/local/ulcc_form_library/classes/form_lib_form.class.php');
require_once("{$CFG->dirroot}/local/ulcc_form_library/lib.php");

/**
 * Form that is used to display the dynamically generated elements to the user for data entry.
 */
class form_entry_mform extends form_lib_form {

    /**
     * @var int
     */
    public $course_id;

    /**
     * @var int
     */
    public $form_id;

    /**
     * @var int
     */
    public $entry_id;

    /**
     * @var string e.g. block, mod
     */
    public $plugintype;

    /**
     * @var string e.g. coursework, ilp
     */
    public $pluginname;

    /**
     * @var form_db
     */
    public $dbc;

    /**
     * @var bool have we already saved the page data and moved to the next page?
     */
    private $pagessaved = false;

    /**
     *
     */
    public function __construct($form_id, $type = false, $name = false, $pageurl = false, $entry_id = null, $page = 1) {

        $this->form_id = $form_id;
        $this->entry_id = $entry_id;
        $this->currentpage = $page;
        $this->pluginname = $name;
        $this->plugintype = $type;

        $this->dbc = new form_db();

        // Call the parent constructor.
        parent::__construct($pageurl);
    }

    /**
     * TODO comment this
     */
    public function definition() {
        global $CFG;

        $mform =& $this->_form;

        // Get all of the fields in the current report, they will be returned in order as
        // no position has been specified.
        $formfields = $this->dbc->get_form_fields_by_position($this->form_id);

        $form = $this->dbc->get_form_by_id($this->form_id);

        // Create a new fieldset.

        $desc = html_entity_decode($form->description);

        $mform->addElement('html', '<div class="descritivetext">' . $desc . '</div>');

        $mform->addElement('hidden', 'entry_id', $this->entry_id);
        $mform->setType('entry_id', PARAM_INT);

        $mform->addElement('hidden', 'form_id', $this->form_id);
        $mform->setType('form_id', PARAM_INT);

        $mform->addElement('hidden', 'current_page', $this->currentpage);
        $mform->setType('current_page', PARAM_INT);

        $mform->addElement('hidden', 'page_data', 1);
        $mform->setType('page_data', PARAM_INT);

        $count = $this->dbc->element_type_exists($this->form_id, 'ulcc_form_plg_pb');
        if ($count) {
            $pagebreakcount = $count;
        }

        // Pre form hook allows any elements to be added to the form by a developer.
        $prehook = $this->pluginname . "_ulcc_pre_form";
        if (function_exists($prehook)) {
            // If a hook function for the current plugin is defined call it.
            call_user_func($prehook,
                array(&$mform,
                    $this->form_id,
                    $formfields));
        }

        $breaksfound = 0;

        if (!empty($formfields)) {

            foreach ($formfields as $field) {

                // Get the plugin record that for the plugin.
                $formelementrecord = $this->dbc->get_formelement_by_id($field->formelement_id);

                // Take the name field from the plugin as it will be used to call the instantiate the plugin class.
                $classname = $formelementrecord->name;

                if ($formelementrecord->tablename == 'ulcc_form_plg_pb') {
                    $breaksfound++;

                    if ($breaksfound == $this->currentpage) {
                        break;
                    }
                }

                if ($breaksfound == $this->currentpage - 1) {
                    // Include the class for the plugin.
                    include_once("{$CFG->dirroot}/local/ulcc_form_library/plugin/form_elements/{$classname}.php");

                    if (!class_exists($classname)) {
                        print_error('noclassforplugin', 'local_ulcc_form_library', '', $formelementrecord->name);
                    }

                    /* @var form_element_plugin $formelementclass */
                    $formelementclass = new $classname();

                    $formelementclass->load($field->id);

                    // Call the plugins entry_form function which will add an instance of the plugin
                    // to the form.
                    $formelementclass->entry_form($mform);
                }
            }
        }

        // Post form hook allows any elements to be added to the form by a developer.
        $posthook = $this->pluginname . "_ulcc_post_form";
        if (function_exists($posthook)) {
            // If a hook function for the current plugin is defined call it.
            call_user_func($posthook,
                array(&$mform,
                    $this->form_id,
                    $formfields));
        }

        // Only show previous if this is not the first page.
        if (!empty($pagebreakcount) && $this->currentpage > 1) {
            $buttonarray[] = &
                $mform->createElement('submit', 'previousbutton', get_string('previous', 'local_ulcc_form_library'));
        }
        if (empty($pagebreakcount) || (!empty($pagebreakcount) && $this->currentpage == $pagebreakcount + 1)) {
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('submit'));
        }
        $buttonarray[] = &$mform->createElement('cancel');

        // Only show next if this is not the last page.
        if (!empty($pagebreakcount) && $this->currentpage != $pagebreakcount + 1) {
            $buttonarray[] = &$mform->createElement('submit', 'nextbutton', get_string('next', 'local_ulcc_form_library'));
        }

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }

    /**
     * @param $data
     * @throws coding_exception
     * @return int Record id or false if there's a problem.
     */
    protected function process_data($data) {

        global $CFG, $USER;

        $data = (!is_object($data)) ? (object)$data : $data;

        local_ulcc_form_library_convert_text_fields($data);
        // Get the id of the report.
        $form_id = $data->form_id;

        // Get the id of the entry  if known.
        $entryid = $data->entry_id;

        if (empty($entry_id)) {
            // Create the entry.
            $entry = new stdClass();
            $entry->form_id = $form_id;
            $entry->creator_id = $USER->id;

            $entryid = $this->dbc->create_entry($entry);

        } else {
            // Update the entry.
            // As there is nothing to update but we want the entries timemodifed
            // to be updated we will just re-add the form_id.
            $entry = new stdClass();
            $entry->id = $entry_id;
            $entry->form_id = $form_id;

            $this->dbc->update_entry($entry); // Will throw exception if there's a problem.
        }

        // Get all of the fields in the current report, they will be returned in order as
        // no position has been specified.
        $formfields = $this->dbc->get_form_fields_by_position($form_id);

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
            /* @var form_element_plugin $pluginclass */
            $pluginclass = new $classname();

            $pluginclass->load($field->id);

            // Call the plugins entry_form function which will add an instance of the plugin
            // to the form.
            if ($pluginclass->is_processable()) {
                if (!$pluginclass->entry_process_data($field->id, $entryid, $data)) {
                    throw new coding_exception('Problem saving form plugin data');
                }
            }
        }

        return $entryid;
    }


    /**
     * Sets the current page to the given number, this must be less than or equal to number of page breaks in
     * the form + 1
     *
     * @param $page
     */
    public function set_current_page($page) {
        $this->currentpage = $page;
    }

    /**
     * Checks whether the next button was pressed and adjusts things.
     */
    public function next() {
        return parent::next($this->form_id, $this->currentpage);
    }

    /**
     * Checks whether the previous biutton was pressed and adjusts things.
     */
    public function previous() {
        return parent::previous($this->form_id, $this->currentpage);
    }

}
