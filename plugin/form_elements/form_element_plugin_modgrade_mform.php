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


    function definition() {
        global $USER, $CFG;

        //get the plugin type by getting the plugin name
        $currentplugin	=	$this->dbc->get_form_element_plugin($this->formelement_id);

        $form           =   $this->dbc->get_form_by_id($this->form_id);

        $mform =& $this->_form;
        $fieldsettitle	=	get_string("addfield",'local_ulcc_form_library');

        //define the elements that should be present on all plugin element forms

        //create a fieldset to hold the form
        $mform->addElement('html', '<fieldset id="formfieldset" class="clearfix formfieldset">');
        $mform->addElement('html', '<legend class="ftoggler">'.$form->name.'</legend>');

        //the id of the form that the element will be in
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('static', 'plugintypestatic',get_string('plugintype','local_ulcc_form_library'),get_string($currentplugin->name.'_type','local_ulcc_form_library'));


        //button to state whether the element is required
        $mform->addElement('checkbox',
            'required',
            get_string('required', 'local_ulcc_form_library')
        );

        $mform->addHelpButton('required', 'required', 'local_ulcc_form_library');

        //button to state whether the element is required
        if ($this->moodlepluginname != 'coursework'){
            $mform->addElement('checkbox',
                'summary',
                get_string('addtosummary', 'local_ulcc_form_library')
            );
        }
        //the id of the form that the element will be in
        $mform->addElement('hidden', 'form_id');
        $mform->setType('form_id', PARAM_INT);
        $mform->setDefault('form_id', $this->form_id);

        //the id of the plugin in use
        $mform->addElement('hidden', 'formelement_id');
        $mform->setType('formelement_id', PARAM_INT);
        $mform->setDefault('formelement_id', $this->formelement_id);

        //the id of the form element creator
        $mform->addElement('hidden', 'creator_id');
        $mform->setType('creator_id', PARAM_INT);
        $mform->setDefault('creator_id', $this->creator_id);

        //the id of the course that the element is being created in
        $mform->addElement('hidden', 'course_id');
        $mform->setType('course_id', PARAM_INT);
        $mform->setDefault('course_id', $this->course_id);


        //the id of the formfield this is only used in edit instances
        $mform->addElement('hidden', 'formfield_id');
        $mform->setType('formfield_id', PARAM_INT);
        $mform->setDefault('formfield_id', $this->formfield_id);

        //the moodle plugin type of the form
        $mform->addElement('hidden', 'moodleplugintype');
        $mform->setType('moodleplugintype', PARAM_INT);
        $mform->setDefault('moodleplugintype', $this->moodleplugintype);

        //the moodle plugin type of the form
        $mform->addElement('hidden', 'moodlepluginname');
        $mform->setType('moodlepluginname', PARAM_RAW);
        $mform->setDefault('moodlepluginname', $this->moodlepluginname);

        //the moodle context_id
        $mform->addElement('hidden', 'context_id');
        $mform->setType('context_id', PARAM_RAW);
        $mform->setDefault('context_id', $this->context_id);


        //the id of the form element creator
        $mform->addElement('hidden', 'position');
        $mform->setType('position', PARAM_INT);
        //set the field position of the field
        $mform->setDefault('position', $this->dbc->get_new_form_field_position($this->form_id));



        //text field for element label
        $mform->addElement(
            'text',
            'label',
            get_string('label', 'local_ulcc_form_library'),
            array('class' => 'form_input')
        );

        $mform->addRule('label', null, 'maxlength', 255, 'client',array('size'=>'10'));
        $mform->addRule('label', null, 'required', null, 'client');
        $mform->setType('label', PARAM_RAW);


        //text field for element description
        $mform->addElement(
            'editor',
            'description',
            get_string('description', 'local_ulcc_form_library'),
            array('class' => 'form_input','rows'=> '10', 'cols'=>'65')
        );

        $mform->addRule('description', null, 'maxlength', 10000, 'client');
        $mform->setType('description', PARAM_RAW);



        $this->specific_definition($mform);

        //add the submit and cancel buttons
        $this->add_action_buttons(true, get_string('submit'));
    }

    /**
     * @param MoodleQuickForm $mform
     */
    protected function specific_definition(MoodleQuickForm $mform) {
        // Grade options will be read from the module this is attached to.
    }

    /**
     * @param $data
     * @return array
     */
    protected function specific_validation($data) {
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
