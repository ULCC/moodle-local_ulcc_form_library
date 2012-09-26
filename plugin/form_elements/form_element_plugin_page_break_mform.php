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

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin_mform.class.php');

/**
 * Allows us to split the form into multiple pages.
 */
class form_element_plugin_page_break_mform extends form_element_plugin_mform {

    /**
     * @param MoodleQuickForm $mform
     */
    protected function specific_definition(MoodleQuickForm $mform) {
        // No need for this in this form element.
    }

    /**
     * @param $data
     * @return array
     */
    protected function specific_validation($data) {

        // No need for this.
        return $this->errors;
    }

    /**
     * @param $data
     * @return bool|mixed
     */
    protected function specific_process_data($data) {

        $plgrec = (!empty($data->formfield_id)) ?
            $this->dbc->get_form_element_record("ulcc_form_plg_pb", $data->formfield_id) : false;

        if (empty($plgrec)) {
            return $this->dbc->create_form_element_record("ulcc_form_plg_pb", $data);
        } else {
            return true;
        }
    }

    /**
     * @param $data
     */
    public function unprocessed_data(&$data) {
        $data->position = $this->dbc->get_new_form_field_position($this->form_id);
        $data->label = '--------';
        $data->form_id = $this->form_id;
        $this->summary = 0;
    }
}
