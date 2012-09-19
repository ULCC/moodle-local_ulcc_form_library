<?php

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin_mform.class.php');

class form_element_plugin_grade_mform  extends form_element_plugin_mform {



    protected function specific_definition(MoodleQuickForm $mform) {

        global  $DB;

        $mform->addElement('html', '<div>'.get_string('form_element_plugin_grade_max100', 'local_ulcc_form_library').'</div>');

        $mform->addElement(
            'text',
            'maxgrade',
            get_string('form_element_plugin_grade_maxgrade', 'local_ulcc_form_library'),
            array('class' => 'form_input')
        );

        $mform->addRule('maxgrade', null, 'numeric', null, 'client');
        $mform->addRule('maxgrade', null, 'required', null, 'client');
        $mform->setType('maxgrade', PARAM_RAW);

    }

    protected function specific_validation($data) {

        $data = (object) $data;

                // The grade cannot be bigger than 100.
        if ( $data->maxgrade >100) {
                  $this->errors['maxgrade'] = get_string('form_element_plugin_grade_maxgrade_error', 'local_ulcc_form_library');
        }
        return $this->errors;
    }

    protected function specific_process_data($data) {

        $plgrec = (!empty($data->formfield_id)) ? $this->dbc->get_form_element_record("ulcc_form_plg_grade", $data->formfield_id) : false;

        if (empty($plgrec)) {
            return $this->dbc->create_form_element_record("ulcc_form_plg_grade", $data);
        } else {
            // Get the old record from the elements plugins table.
            $oldrecord = $this->dbc->get_form_element_by_formfield("ulcc_form_plg_grade", $data->formfield_id);

            // Create a new object to hold the updated data.
            $pluginrecord = new stdClass();
            $pluginrecord->id = $oldrecord->id;

            // Update the plugin with the new data.
            return $this->dbc->update_form_element_record("ulcc_form_plg_grade", $pluginrecord);
        }
    }

    function definition_after_data() {

    }



}
