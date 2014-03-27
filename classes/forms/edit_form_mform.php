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
 * This class makes the form that is used to create forms
 *
 * @copyright &copy; 2011 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package form library
 * @version 1.0
 */

global $CFG;

require_once("$CFG->libdir/formslib.php");
require_once("{$CFG->dirroot}/local/ulcc_form_library/lib.php");

/**
 * Edits the name and description of a form.
 */
class edit_form_mform extends moodleform {

    /**
     * @var int
     */
    public $form_id;

    public $pluginname;
    public $plugintype;
    public $context_id;

    /**
     * @var form_db
     */
    public $dbc;

    public $templates;

    /**
     * TODO comment this
     */
    public function __construct($pluginname, $plugintype, $context_id, $form_id = null) {

        global $CFG;

        $this->form_id = $form_id;
        $this->pluginname = $pluginname;
        $this->plugintype = $plugintype;
        $this->context_id = $context_id;

        $this->dbc = new form_db();

        // Lets check if the plugin has been created in the form library plugin table.
        $moodleplugin = $this->dbc->get_moodle_plugin($pluginname, $plugintype);

        $this->plugin_id = (!empty($moodleplugin)) ? $moodleplugin->id : null;

        // Call the parent constructor.
        parent::__construct("{$CFG->wwwroot}/local/ulcc_form_library/actions/edit_form.php?form_id={$this->form_id}&moodleplugintype={$this->plugintype}&moodlepluginname={$this->pluginname}&context_id={$this->context_id}");
    }

    /**
     * TODO comment this
     */
    public function definition() {
        global $USER, $CFG;

        $mform =& $this->_form;

        $fieldsettitle = (!empty($this->form_id)) ? get_string('editform', 'local_ulcc_form_library') :
            get_string('createform', 'local_ulcc_form_library');

        // Create a new fieldset.
        $mform->addElement('html', '<fieldset id="reportfieldset" class="clearfix ilpfieldset">');
        $mform->addElement('html', '<legend >'.$fieldsettitle.'</legend>');

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'creator_id', $USER->id);
        $mform->setType('creator_id', PARAM_INT);

        $mform->addElement('hidden', 'moodlepluginname', $this->pluginname);
        $mform->setType('moodlepluginname', PARAM_TEXT);

        $mform->addElement('hidden', 'moodleplugintype', $this->plugintype);
        $mform->setType('moodleplugintype', PARAM_TEXT);

        $mform->addElement('hidden', 'context_id', $this->context_id);
        $mform->setType('context_id', PARAM_INT);

        $mform->addElement('hidden', 'plugin_id', $this->plugin_id);
        $mform->setType('plugin_id', PARAM_INT);

        // The id of the form element creator.
        $mform->addElement('hidden', 'position');
        $mform->setType('position', PARAM_INT);
        // Set the field position of the field.
        $mform->setDefault('position', $this->dbc->get_new_form_position($this->plugintype, $this->pluginname));

        // NAME element.
        $mform->addElement(
            'text',
            'name',
            get_string('name', 'local_ulcc_form_library'),
            array('class' => 'form_input')
        );
        $mform->addRule('name', null, 'maxlength', 255, 'client');
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->setType('name', PARAM_TEXT);

        // DESCRIPTION element.
        $mform->addElement(
            'editor',
            'description',
            get_string('description', 'local_ulcc_form_library'),
            array('class' => 'form_input',
                  'rows' => '10',
                  'cols' => '65')
        );

        //  $mform->addRule('description', null, 'maxlength', 65535, 'client');

        $mform->setType('description', PARAM_RAW);

        // TODO add the elements to implement the frequency functionlaity.
        if (stripos($CFG->release, "2.") !== false) {
            $mform->addElement('filepicker', 'binary_icon', get_string('binary_icon', 'local_ulcc_form_library'), null,
                               array('maxbytes' => FORM_MAXFILE_SIZE,
                                     'accepted_types' => FORM_ICON_TYPES));
        } else {
            $this->set_upload_manager(new upload_manager('binary_icon', false, false, 0, false, FORM_MAXFILE_SIZE, true, true, false));
            $mform->addElement('file', 'binary_icon', get_string('binary_icon', 'local_ulcc_form_library'));
        }

        $mform->addElement(
            'text',
            'identifier',
            get_string('identifier', 'local_ulcc_form_library'),
            array('class' => 'form_input')
        );
        $mform->setType('identifier', PARAM_TEXT);

        $buttonarray[] = $mform->createElement('submit', 'saveanddisplaybutton', get_string('submit'));
        $buttonarray[] = &$mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);

        // Close the fieldset.
        $mform->addElement('html', '</fieldset>');
    }

    /**
     * @param array $data
     * @param array $files
     * @return array|void
     */
    public function validation($data, $files) {
        $data = (object)$data;
        $this->errors = array();
    }

    /**
     * TODO comment this
     */
    public function process_data($data) {

        local_ulcc_form_library_convert_text_fields($data);
        if (empty($data->id)) {

            $data->id = $this->dbc->create_form($data);
        } else {

            // Check to stop report icons from being overwritten
            // if the binary_icon param is empty unset it that will stop
            // any data that is currently present from being overwritten.
            if (empty($data->binary_icon)) {
                unset($data->binary_icon);
            }

            $this->dbc->update_form($data);
        }

        return $data->id;
    }

}

