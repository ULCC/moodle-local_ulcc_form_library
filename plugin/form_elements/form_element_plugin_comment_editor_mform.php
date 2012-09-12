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
 * Plugin file for the class that makes a comment editor for the gradebook
 *
 * @package    local
 * @subpackage ulcc_form_library
 * @copyright  2012 University of London Computer Centre {@link ulcc.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin_mform.class.php');

/**
 * There ought to only ever be one of these so that if there's a need to add a grade to the gradebook, then this is the one of
 * potentially many editors that will be used.
 */
class form_element_plugin_comment_editor_mform extends form_element_plugin_mform {

    /**
     * @param MoodleQuickForm $mform
     */
    protected function specific_definition(MoodleQuickForm $mform) {

        // Disabled until http://tracker.moodle.org/browse/MDL-35402 is fixed.

        // Set the maximum length of the field default to 255.
//        $mform->addElement(
//            'text',
//            'minimumlength',
//            get_string('form_element_plugin_comment_editor_minimumlength', 'local_ulcc_form_library'),
//            array('class' => 'form_input')
//        );
//
//        $mform->addRule('minimumlength', null, 'maxlength', 3, 'client');
//        // $mform->addRule('minimumlength', null, 'required', null, 'client');
//        $mform->setType('minimumlength', PARAM_INT);
//
//        // Set the maximum length of the field default to 255.
//        $mform->addElement(
//            'text',
//            'maximumlength',
//            get_string('form_element_plugin_comment_editor_maximumlength', 'local_ulcc_form_library'),
//            array('class' => 'form_input')
//        );
//
//        $mform->addRule('maximumlength', null, 'maxlength', 4, 'client');
//        // $mform->addRule('maximumlength', null, 'required', null, 'client');
//        $mform->setType('maximumlength', PARAM_INT);
    }

    /**
     * @param $data
     * @return array
     */
    protected function specific_validation($data) {

        $data = (object)$data;

        if ($data->maximumlength < 0 || $data->maximumlength > 9999) {
            $this->errors['maximumlength'] =
                get_string('form_element_plugin_comment_editor_maxlengthrange', 'local_ulcc_form_library');
        }
        if ($data->maximumlength < $data->minimumlength) {
            $this->errors['maximumlength'] =
                get_string('form_element_plugin_comment_editor_maxlessthanmin', 'local_ulcc_form_library');
        }

        return $this->errors;
    }

    /**
     * @param $data
     * @return bool|mixed
     */
    protected function specific_process_data($data) {

        $plgrec =
            (!empty($data->formfield_id)) ? $this->dbc->get_form_element_record("ulcc_form_plg_cedt", $data->formfield_id) : false;

        if (empty($plgrec)) {
            return $this->dbc->create_form_element_record("ulcc_form_plg_cedt", $data);
        } else {
            // Get the old record from the elements plugins table.
            $oldrecord = $this->dbc->get_form_element_by_formfield("ulcc_form_plg_cedt", $data->formfield_id);

            // Create a new object to hold the updated data.
            $pluginrecord = new stdClass();
            $pluginrecord->id = $oldrecord->id;
            $pluginrecord->minimumlength = $data->minimumlength;
            $pluginrecord->maximumlength = $data->maximumlength;

            // Update the plugin with the new data.
            return $this->dbc->update_form_element_record("ulcc_form_plg_cedt", $pluginrecord);
        }
    }

}
