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

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin_mform.class.php');

/**
 * Form field that allows a grade to be added that's specific to one module. This is so that a grade can be collected and
 * sent to the gradebook.
 */
class form_element_plugin_modgrade_mform extends form_element_plugin_mform {

    /**
     * @param MoodleQuickForm $mform
     */
    protected function specific_definition(MoodleQuickForm $mform) {

        global $DB;

        $mform->addElement('html',
                           '<div>'.get_string('form_element_plugin_modgrade_dynamicdesc', 'local_ulcc_form_library').'</div>');

        $mform->addElement('advcheckbox', 'gradetype',
                           get_string('form_element_plugin_modgrade_gradetype', 'local_ulcc_form_library'), '', array('group' => 1),
                           array(0,
                                 1));

        $modules = $DB->get_records('modules', array('visible' => 1));

        $options = array();

        foreach ($modules as $m) {
            $options[$m->name] = $m->name;
        }

        $mform->addElement('select', 'tablename', get_string('form_element_plugin_modgrade_module', 'local_ulcc_form_library'),
                           $options);

        $mform->setDefault('tablename', $this->moodlepluginname);

        $mform->disabledIf('tablename', 'gradetype', 'unchecked');

        $scales = $DB->get_records('scale');
        $options = array();

        foreach ($scales as $s) {
            $options[$s->id] = $s->name;
        }

        $mform->addElement('select', 'gradescale', get_string('form_element_plugin_modgrade_gradescale', 'local_ulcc_form_library'),
                           $options);

        $mform->disabledIf('gradescale', 'gradetype', 'checked');
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function specific_validation($data) {

        $data = (object)$data;

        return $this->errors;
    }

    /**
     * @param $data
     * @return bool|mixed
     */
    protected function specific_process_data($data) {

        $plgrec =
            (!empty($data->formfield_id)) ? $this->dbc->get_form_element_record("ulcc_form_plg_modgd", $data->formfield_id) : false;

        if (empty($plgrec)) {
            return $this->dbc->create_form_element_record("ulcc_form_plg_modgd", $data);
        } else {
            // Get the old record from the elements plugins table.
            $oldrecord = $this->dbc->get_form_element_by_formfield("ulcc_form_plg_modgd", $data->formfield_id);

            // Create a new object to hold the updated data.
            $pluginrecord = new stdClass();
            $pluginrecord->id = $oldrecord->id;

            // Update the plugin with the new data.
            return $this->dbc->update_form_element_record("ulcc_form_plg_modgd", $pluginrecord);
        }
    }

}
