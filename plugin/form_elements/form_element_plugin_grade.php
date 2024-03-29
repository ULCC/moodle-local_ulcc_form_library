<?php

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin.class.php');

class form_element_plugin_grade extends form_element_plugin {

    public $tablename;
    public $data_entry_tablename;

    /**
     * Constructor
     */
    function __construct() {

        $this->tablename = "ulcc_form_plg_grade";
        $this->data_entry_tablename = "ulcc_form_plg_grade_ent";

        parent::__construct();
    }


    /**
     * TODO comment this
     * called when user form is submitted
     */
    public function load($formfield_id) {
        $formfield = $this->dbc->get_form_field_data($formfield_id);
        if (!empty($formfield)) {
            // Set the formfield_id var.
            $this->formfield_id = $formfield_id;

            // Get the record of the plugin used for the field.
            $plugin = $this->dbc->get_form_element_plugin($formfield->formelement_id);

            $this->formelement_id = $formfield->formelement_id;

            // Get the form element record for the formfield.
            $pluginrecord = $this->dbc->get_form_element_by_formfield($this->tablename, $formfield->id);

            if (!empty($pluginrecord)) {
                $this->label = $formfield->label;
                $this->description = $formfield->description;
                $this->required = $formfield->required;
                $this->maxgrade = !empty($pluginrecord->maxgrade) ? $pluginrecord->maxgrade : 0;
                $this->position = $formfield->position;
                $this->audit_type = $this->audit_type();
                return true;
            }
        }
        return false;
    }


    /**
     * create tables for this plugin
     */
    public function install() {
        global $CFG, $DB;

        // Create the table to store form fields.
        $table = new $this->xmldb_table( $this->tablename );
        $set_attributes = method_exists($this->xmldb_key, 'set_attributes') ? 'set_attributes' : 'setAttributes';

        $table_id = new $this->xmldb_field('id');
        $table_id->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addField($table_id);

        $table_form = new $this->xmldb_field('formfield_id');
        $table_form->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_form);


        $table_form = new $this->xmldb_field('maxgrade');
        $table_form->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_form);

        $table_timemodified = new $this->xmldb_field('timemodified');
        $table_timemodified->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_timemodified);

        $table_timecreated = new $this->xmldb_field('timecreated');
        $table_timecreated->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_timecreated);

        $table_key = new $this->xmldb_key('primary');
        $table_key->$set_attributes(XMLDB_KEY_PRIMARY, array('id'));
        $table->addKey($table_key);

        $table_key = new $this->xmldb_key('grade_unique_formfield');
        $table_key->$set_attributes(XMLDB_KEY_FOREIGN_UNIQUE, array('formfield_id'), 'local_ulcc_form_library_form_field', 'id');
        $table->addKey($table_key);

        if (!$this->dbman->table_exists($table)) {
            $this->dbman->create_table($table);
        }


        // Create the new table to store responses to fields.
        $table = new $this->xmldb_table( $this->data_entry_tablename );
        $set_attributes = method_exists($this->xmldb_key, 'set_attributes') ? 'set_attributes' : 'setAttributes';

        $table_id = new $this->xmldb_field('id');
        $table_id->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addField($table_id);

        $table_title = new $this->xmldb_field('value');
        $table_title->$set_attributes(XMLDB_TYPE_CHAR, 255, null, null);
        $table->addField($table_title);

        $table_form = new $this->xmldb_field('entry_id');
        $table_form->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_form);

        $table_maxlength = new $this->xmldb_field('parent_id');
        $table_maxlength->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_maxlength);

        $table_timemodified = new $this->xmldb_field('timemodified');
        $table_timemodified->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_timemodified);

        $table_timecreated = new $this->xmldb_field('timecreated');
        $table_timecreated->$set_attributes(XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL);
        $table->addField($table_timecreated);

        $table_key = new $this->xmldb_key('primary');
        $table_key->$set_attributes(XMLDB_KEY_PRIMARY, array('id'));
        $table->addKey($table_key);

        $table_key = new $this->xmldb_key($this->tablename.'_foreign_key');
        $table_key->$set_attributes(XMLDB_KEY_FOREIGN, array('parent_id'), $this->tablename , 'id');
        $table->addKey($table_key);

        if (!$this->dbman->table_exists($table)) {
            $this->dbman->create_table($table);
        }


    }

    /**
     *
     */
    public function uninstall() {
        $table = new $this->xmldb_table( $this->tablename );
        drop_table($table);

        $table = new $this->xmldb_table( $this->data_entry_tablename );
        drop_table($table);
    }
     /**
      *
      */
    public function audit_type() {
        return get_string('form_element_plugin_grade_type', 'local_ulcc_form_library');
    }

    /**
     * function used to return the language strings for the plugin
     */
    function language_strings(&$string) {
        $string['form_element_plugin_grade'] = 'Grade selector';
        $string['form_element_plugin_grade_type']    = 'Grade selector';
        $string['form_element_plugin_grade_description'] = 'A grade selector';
        $string['form_element_plugin_grade_select_value'] = 'Select Value';
        $string['form_element_plugin_grade_max100'] =   'The maximum grade cannot be bigger than 100 ';
        $string['form_element_plugin_grade_maxgrade'] =   'Enter maximum possible grade';
        $string['form_element_plugin_grade_maxgrade_error'] =  'The maximum grade is bigger than 100';


        return $string;
    }

     /**
      * Delete a form element
      */
    public function delete_form_element($formfield_id, $tablename=null, $extraparams=null) {
        $formfield = $this->dbc->get_form_field_data($formfield_id);
        $extraparams = array(
            'audit_type' => $this->audit_type(),
            'label' => $formfield->label,
            'description' => $formfield->description,
            'id' => $formfield_id
        );
        return parent::delete_form_element($formfield_id, $this->tablename, $extraparams);
    }

    /**
     * this function returns the mform elements taht will be added to a form form
     *
     */
    public function entry_form( &$mform ) {
        global  $DB;

        $fieldname = "{$this->formfield_id}_field";

        if (!empty($this->description)) {
            $mform->addElement('static', "{$fieldname}_desc", $this->label, strip_tags(html_entity_decode($this->description), FORM_STRIP_TAGS_DESCRIPTION));
                    $this->label = '';
        }

        // Retrieve max value entered by the user.
        $plugrecord= $this->dbc->get_form_element_by_formfield($this->tablename, $this->formfield_id);
        $maxgrade = empty($plugrecord->maxgrade) ? 0 : $plugrecord->maxgrade;
        $gradelist = array();
        // Create list of grades.
        $gradelist[''] = get_string('form_element_plugin_grade_select_value', 'local_ulcc_form_library');
        for ($i=$maxgrade; $i>=0; $i--) {
             $gradelist[$i] = $i;
        }

        // Text field for element label.
        $select = &$mform->addElement(
            'select',
            $fieldname,
            $this->label,
            $gradelist,
            array('class' => 'form_input')
        );


        if (!empty($this->required)) {
            $mform->addRule($fieldname, null, 'required', null, 'client');
        }

    }

    /**
     * handle user input
     **/
    public function entry_specific_process_data($formfield_id, $entry_id, $data) {
        /*
        * parent method is fine for simple form element types
        * dd types will need something more elaborate to handle the intermediate
        * items table and foreign key
        */
            return $this->entry_process_data($formfield_id, $entry_id, $data);
    }

    /**
     * Determines whether a user can add a new instance of the mod grade plugin to the form
     *
     * @param int $form_id the id of the form that will be checked to see if it has the element
     * @return bool
     */
    function can_add($form_id) {
        return !$this->dbc->element_type_exists( $form_id, $this->tablename );
    }

}
