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
 * @copyright &copy; 2012 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package form
 * @version
 */

global $CFG;

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_logging.class.php');
require_once($CFG->dirroot.'/local/ulcc_form_library/constants.php');

/**
 * Provides database access functions for the forms library.
 */
class form_db extends form_logging {

    /**
     * Constructor for the form_db_functions class
     *
     * @return \form_db
     */
    public function __construct() {
        global $DB;

        $this->dbc = $DB;
        // Include the static constants.

    }

   /**
     * finds and returns a block record from the db using the blocks name
     *
     * @param string $name - the name of the block
     *
     * return mixed object containing the block record or bool false
     * @return mixed
     */
    public function get_block_by_name($name) {
        return $this->dbc->get_record('block', array('name' => $name));
    }

    /**
     * finds and returns a modules record from the db using the modules name
     *
     * @param string $name - the name of the module
     *
     * return mixed object containing module record or bool false
     * @return mixed
     */
    public function get_mod_by_name($name) {
        return $this->dbc->get_record('modules', array('name' => $name));
    }

    /**
     *  creates a record in the ulcc_form_lib_plugin table using the details in the given object
     *
     * @param object $pluginrecord    - object containing details of plugin record that will inserted into
     *                                   db
     *
     *  return mixed int id number of new record or bool false
     * @return mixed
     */
    public function create_plugin($pluginrecord) {
        return $this->insert_record('ulcc_form_lib_plugin', $pluginrecord);
    }

    /**
     * Returns all forms that have been created for the plugin with the given
     * name and type.
     *
     * @param string $name  -   the name of the plugin whose forms will be returned
     * @param string $type  -   the type of the plugin that whose forms will be returned
     * @param string $formtype - the type of the form that will be returned
     *
     * @param bool $disabled
     * @return mixed array containing form objects or bool false
     */
    public function get_plugin_forms($name, $type, $formtype = null, $disabled = false) {

        $sqlparams = array('name' => $name,
                           'type' => $type);

        $formsql = '';

        if ((!empty($formtype))) {
            $formsql = " AND f.type =   :ftype ";
            $sqlparams['ftype'] = $formtype;
        }

        if (empty($disabled)) {
            $formsql .= " AND f.status = 1 ";
        }

        $sql = "SELECT   f.*
                     FROM     {ulcc_form_lib_plugin}  as p,
                              {ulcc_form_lib_form}    as f
                     WHERE    p.name    =   :name
                     AND      p.type    =   :type
                     AND      p.id      =   f.plugin_id
                     AND      f.deleted != 1
                     {$formsql}
                    ";

        return $this->dbc->get_records_sql($sql, $sqlparams);
    }

    /**
     * Returns all forms that have been created for the plugin with the given
     * id.
     *
     * @param string $plugin_id the id of the plugin that the returned forms where created for
     *
     * @return mixed array containing form objects or bool false
     */
    public function get_plugin_forms_by_id($plugin_id) {
        return $this->dbc->get_records('ulcc_form_lib_form', array('plugin_id' => $plugin_id));
    }

    /**
     * Returns the form with the id given.
     *
     * @param $form_id  -   the id of the form whose record will be returned
     * @return mixed object contain form data or false
     */
    public function get_form_by_id($form_id) {
        return $this->dbc->get_record('ulcc_form_lib_form', array('id' => $form_id));
    }

    /**
     * Returns a the moodle plugin record that matches the given parameters
     * in the ulcc_form_lib_plugin
     * @param $name     -   the name of the plugijn that will be returned
     * @param $type     -   the type of the plugin
     * @return mixed object the plugin record or false
     */
    public function get_moodle_plugin($name, $type) {
        return $this->dbc->get_record('ulcc_form_lib_plugin',
                                      array('name' => $name,
                                            'type' => $type));
    }

    /**
     * Gets the full list of form element plugins currently installed.
     *
     * @return array Result objects
     */
    public function get_form_element_plugins() {
        global $DB;

        //this must be done to prevent errors when the plugin is being installed
        //as the table will not exist
        $tableexists = in_array('ulcc_form_lib_form_element', $DB->get_tables());

        return (!empty($tableexists)) ? $this->dbc->get_records('ulcc_form_lib_form_element') : false;
    }

    /**
     * returns data that can be used with a pagable ilp_flexible_table
     *
     * @param object $flextable an object of type flextable
     * @param $pluginname
     * @param $type
     * @param boolean $deleted should deleted reports be returned
     *                   defaults to false
     * @return mixed object containing report records or false
     */
    public function get_forms_table($flextable, $pluginname, $type, $deleted = false) {
        global $CFG;

        $select = "SELECT		f.* ";

        $from = "FROM 		{ulcc_form_lib_form} as f,
                                {ulcc_form_lib_plugin} as p ";

        $where = "WHERE      p.name     =    :pname
                     AND        p.type     =    :ptype
                     AND        p.id       =    f.plugin_id";

        $where .= (empty($deleted)) ? " AND   deleted != 1 " : "";

        $order = " ORDER BY 	position";

        // get a count of all the records for the pagination links
        $count = $this->dbc->count_records_sql('SELECT COUNT(*) '.$from.$where,
                                               array('pname' => $pluginname,
                                                     'ptype' => $type));

        // tell the table how many pages it needs
        //$flextable->totalrows($count);

        $data = $this->dbc->get_records_sql(
            $select.$from.$where.$order,
            array('pname' => $pluginname,
                  'ptype' => $type),
            $flextable->get_page_start(),
            $flextable->get_page_size()
        );

        return $data;
    }

    /**
     * Returns the course record for the course with the given id
     *
     * @param int $courseid the id of the course whose database record will be retrieved
     *
     * @return mixed object containing course record or bool false
     */
    public function get_course_by_id($courseid) {
        return $this->dbc->get_record('course', array('id' => $courseid));
    }

    /**
     * Creates a form record in the ulcc_form_lib_form table
     *
     * @param   object  $form   the form that will be created
     * @return  mixed int the id of the new record or bool false
     */
    public function create_form($form) {
        return $this->insert_record('ulcc_form_lib_form', $form);
    }

    /**
     * Updates a form record
     *
     * @param   object  $form   the updated form record
     * @return   bool true if updates successful or false if not
     */

    public function update_form($form) {
        return $this->update_record('ulcc_form_lib_form', $form);
    }

    /**
     *
     * Returns all courses that the user with the given id is enrolled
     * in
     *
     * @param int $user_id    the id of the user whose course we will retrieve
     *
     * @return  array of recordset objects or bool false
     */
    public function get_user_courses($user_id) {
        global $CFG;

        if (stripos($CFG->release, "2.") !== false) {
            $courses = enrol_get_users_courses($user_id, false, NULL, 'fullname DESC');
        } else {
            $courses = get_my_courses($user_id);
        }

        return $courses;
    }

    /**
     * Returns all courses in the current moodle
     *
     * @return mixed object containing all course records or false
     */
    public function get_courses() {
        return $this->dbc->get_records("course", array(), 'fullname ASC');
    }

    /**
     * Create a plugin entry in the table given
     *
     * @param $tablename
     * @param $pluginentry
     * @return mixed int id of new reocrd or false
     */
    public function create_plugin_entry($tablename, $pluginentry) {
        return $this->insert_record($tablename, $pluginentry);
    }

    /**
     * Update a plugin entry record in the table given
     *
     * @param $tablename
     * @param $pluginentry
     * @return bool true or false
     */
    public function update_plugin_entry($tablename, $pluginentry) {
        return $this->update_record($tablename, $pluginentry);
    }

    /**
     * Get the data entry record with the id given
     *
     * @param string tablename the name of the table that will be interrogated
     * @param int     $entry_id the entry id of the records that will be returned
     * @param int     $formfield_id the id of the form field
     * @param bool     $multiple is there a chance multiple records will be return
     * if yes set mutliple to true
     * @return mixed object the entry record or false
     */
    public function get_pluginentry($tablename, $entry_id, $formfield_id, $multiple = false) {
        global $CFG;

        $entrytable = "{$CFG->prefix}{$tablename}_ent";
        $parenttable = "{$CFG->prefix}{$tablename}";

        $itemtable = (!empty($multiple)) ? "{$CFG->prefix}{$tablename}_items as i," : '';
        // For single items, entries are attached to parent. For multiples, the items table sits in between them.
        $where = (!empty($multiple)) ? "e.parent_id	=	i.id AND i.parent_id	=	p.id" : "e.parent_id	=	p.id";

        $sql = "SELECT		*
                 FROM 		{$parenttable} as p,
                            {$itemtable}
                            {$entrytable} as e
                 WHERE 		{$where}
                 AND		e.entry_id	=	{$entry_id}
                 AND		p.formfield_id	=	{$formfield_id}";

        return (empty($multiple)) ? $this->dbc->get_record_sql($sql) : $this->dbc->get_records_sql($sql);
    }

    /**
     * Returns the record from the given ilp form element plugin table with the formfield_id given
     *
     * @param int    $formfield_id the id of the element in the given table
     * @param string $tablename the name of the plugin table that holds the data that will be retrieved
     * @return object containing plugin record that matches criteria
     */
    public function get_form_element_by_formfield($tablename, $formfield_id) {
        return $this->dbc->get_record($tablename, array("formfield_id" => $formfield_id));
    }

    /**
     * Returns all forms with a position less than or greater than
     * depending on type given. the results will include the position as well.
     * if position and type are not specified all forms are returned ordered by
     * position
     *
     * @param $mplugname
     * @param $mplugtype
     * @param int $position the position of fields that will be returned
     *      greater than or less than depending on $type
     * @param  int $type determines whether fields returned will be greater than
     *         or less than position. move up = 1 move down 0
     * @param bool $disabled should disabled forms be returned
     * @return mixed object containing the plugin record or false
     */
    public function get_forms_by_position($mplugname, $mplugtype, $position = null, $type = null, $disabled = true) {
        global $CFG;

        $positionsql = "";

        $params = array('name' => $mplugname,
                        'type' => $mplugtype);

        //the operand that will be used
        if (!empty($position)) {
            $params['otherfield'] = (!empty($type)) ? $position - 1 : $position + 1;
            $positionsql = "AND (position = {$position} ||  position = :otherfield";
        }

        $disabledsql = '';
        if (empty($disabled)) {
            $disabledsql = "AND status = 1 ";
        }

        $sql = "SELECT		*
					 FROM		{ulcc_form_lib_form} as f
					            {ulcc_form_lib_plugin} as p
					 WHERE      name  = :name
					 AND        type  = :type
					 AND        p.id  = f.plugin_id
					 AND        deleted = 0
                     {$disabledsql}
					 {$positionsql}
					 ORDER BY 	position";

        return $this->dbc->get_records_sql($sql, $params);
    }

    /**
     * Sets the new position of a form
     *
     * @param int $form_id the id of the report whose position will be changed
     * @param $newposition
     * @return mixed object containing the plugin record or false
     */
    public function set_new_form_position($form_id, $newposition) {
        return $this->dbc->set_field('ulcc_form_lib_form', "position", $newposition, array('id' => $form_id));
    }

    /**
     * Creates a new form element plugin record.
     *
     * @param string $name the name of the new form element plugin
     * @param $tablename
     * @return mixed the id of the inserted record or false
     */
    public function create_form_element_plugin($name, $tablename) {
        $type = new stdClass();
        $type->name = $name;
        $type->tablename = $tablename;

        //TODO: should form element be enabled by default?
        $type->status = 1;

        return $this->insert_record('ulcc_form_lib_form_element', $type);
    }

    /**
     * This function sets the status of a form to enabled or disabled
     *
     * @param $form_id
     * @param $status
     * @return    mixed  object containing the record or bool false
     */
    public function set_form_status($form_id, $status) {
        return $this->dbc->set_field('ulcc_form_lib_form', 'status', $status, array('id' => $form_id));
    }

    /**
     * Returns all fields in a form with a position less than or greater than
     * depending on type given. the results will include the position as well.
     * if position and type are not specified all fields are returned ordered by
     * position
     *
     * @param int $form_id the id of the report whose fields will be returned
     * @param int $position the position of fields that will be returned
     *      greater than or less than depending on $type
     * @param  int $type determines whether fields returned will be greater than
     *         or less than position. move up = 1 move down 0
     * @return mixed object containing the plugin record or false
     */
    public function get_form_fields_by_position($form_id, $position = null, $type = null) {

        $positionsql = "";

        //the operand that will be used
        if (!empty($position)) {
            $otherfield = (!empty($type)) ? $position - 1 : $position + 1;
            $positionsql = "AND (position = {$position} ||  position = {$otherfield})";
        }

        $sql = "SELECT		*
					 FROM		{ulcc_form_lib_form_field}
					 WHERE		form_id	=	{$form_id}
					{$positionsql}
					 ORDER BY 	position";

        return $this->dbc->get_records_sql($sql);
    }

    /**
     * Sets the new position of a field
     *
     * @param int $formfield_id the id of the formfield whose position will be changed
     * @param $newposition
     * @return mixed object containing the plugin record or false
     */
    public function set_new_position($formfield_id, $newposition) {
        return $this->dbc->set_field('ulcc_form_lib_form_field', "position", $newposition, array('id' => $formfield_id));
    }

    /**
     * Returns the form element plugin record that has the matching id
     *
     * @param int $formelement_id the id of the plugin that will be retrieved
     * @return mixed object containing the plugin record or false
     */
    public function get_formelement_by_id($formelement_id) {
        return $this->dbc->get_record('ulcc_form_lib_form_element', array('id' => $formelement_id));
    }

    /**
     * Returns the form field record with the id given
     *
     * @param int    $formfield_id the id of the report field that you want to get the data on
     * @return object containing data from the report field record that matches criteria
     */
    public function get_form_field_data($formfield_id) {
        return $this->dbc->get_record("ulcc_form_lib_form_field", array("id" => $formfield_id));
    }



    /**
     * Returns fields of the the given report
     *
     * @param int    $form_id the id of the report
     * @return mixed array of objects
     */
    public function get_form_fields_by_form_id($form_id){
        return $this->dbc->get_records("ulcc_form_lib_form_field", array("form_id" => $form_id));
    }

    /** Returns latest position of the report in the table
     * @return mixed object
     */
    public function get_form_latest_position(){

        $sql =  "SELECT *
				FROM {ulcc_form_lib_form}
				ORDER BY position DESC
				LIMIT 1";

        return $this->dbc->get_record_sql($sql);
}

    /**
     * Gets the record with id matching the given formelement_id
     *
     * @param int $formelement_id the id of the form element to be returned
     * @return mixed the id of the inserted record or false
     */
    public function get_form_element_plugin($formelement_id) {
        return $this->dbc->get_record("ulcc_form_lib_form_element", array('id' => $formelement_id));
    }

    /**
     * Returns the position number a new report field should take
     *
     * @param $form_id
     * @internal param int $report_id the id of the report that the new field will be in
     * @return int the new fields position number
     */

    public function get_new_form_field_position($form_id) {

        $position = $this->dbc->count_records("ulcc_form_lib_form_field", array("form_id" => $form_id));

        return (empty($position)) ? 1 : $position + 1;
    }

    /**
     * Creates a new record in the given plugin table
     *
     * @param string $tablename the name of the table that will be updated
     * @param object $formelementrecord an object containing the data on the record
     * @return mixed the id of the inserted record or false
     */
    public function create_form_element_record($tablename, $formelementrecord) {
        return $this->insert_record($tablename, $formelementrecord);
    }

    /**
     * Updates the given record in the given table
     *
     * @param string $tablename the name of the table that will be updated
     * @param object $formelementrecord an object containing the data on the record
     * @return bool true or false depending on result of query
     */
    public function update_form_element_record($tablename, $formelementrecord) {
        return $this->update_record($tablename, $formelementrecord);
    }

    /**
     * Used to check if a form field with the given label already exists in the report
     * with the given form_id
     *
     * @param    string $label    the label that is being test to see if it exists
     * @param    int $form_id the id of the report that will be checked
     *
     * @param $field_id
     * @return    mixed array of recordsets or bool false
     */
    public function label_exists($label, $form_id, $field_id) {

        $label = mysql_real_escape_string($label);

        //this code is needed due to a substr_count in the
        //moodle_database.php file (line 666 :-( ) it causes
        //an error whenever a label has an ? in it
        $label = str_replace('?', '.', $label);

        $params = array('label' => $label,
                        'formid' => $form_id);

        $currentfieldsql = '';

        if (!empty($field_id)) {
            $currentfieldsql = "AND id != :field_id";
            $params['field_id'] = $field_id;
        }

        $sql = 'SELECT		*
  					 FROM		{ulcc_form_lib_form_field}
  					 WHERE		label		=	:label
  					 AND		form_id	=	:formid '
            .$currentfieldsql;

        return $this->dbc->get_records_sql($sql, $params);
    }

    /**
     * Creates a new form field record
     *
     * @param object $formfield an object containing the data to be saved
     * @return mixed the id of the inserted record or false
     */
    public function create_form_field($formfield) {
        return $this->insert_record("ulcc_form_lib_form_field", $formfield);
    }

    /**
     * Updates the record in the form field table with a id matching the one
     * in the given object
     *
     * @param object $formfield an object containing the data on the record
     * @return bool true or false depending on result of query
     */
    public function update_form_field($formfield) {
        return $this->update_record('ulcc_form_lib_form_field', $formfield);
    }

    /**
     * Get the plugin instance record that has the formfield_id given
     *
     * @param string $tablename the name of the table that will be updated
     * @param int $formfield_id the formfield_id that the record must have
     * @return mixed object containing the plugin instance record or false
     */
    public function get_form_element_record($tablename, $formfield_id) {
        return $this->dbc->get_record($tablename, array('formfield_id' => $formfield_id));
    }

    /**
     * Delete the record from the given table with the formfield_id matching the given id
     *
     * @param string $tablename the table that you want to delete the record from
     * @param int $id the id of the record that you want to delete
     *
     * @param array $extraparams
     * @return bool true or false
     */
    public function delete_form_element_by_formfield($tablename, $id, $extraparams = array()) {
        return $this->delete_records($tablename, array('formfield_id' => $id), $extraparams);
    }

    /**
     * Delete a report field record
     *
     * @param int $id the id of the record that you want to delete
     *
     * @param array $extraparams
     * @return bool true or false
     */
    public function delete_form_field($id, $extraparams = array()) {
        return $this->delete_records('ulcc_form_lib_form_field', array('id' => $id), $extraparams);
    }

    /**
     * This function sets the delete field of a reportd
     *
     * @param $form_id
     * @param $deleted
     * @return    mixed  object containing the record or bool false
     */
    public function delete_form($form_id, $deleted) {
        return $this->dbc->set_field('ulcc_form_lib_form', 'deleted', $deleted, array('id' => $form_id));
    }

    /**
     * Get the form element instance record that has the formfield_id given
     *
     * @param string $tablename the name of the table that will be updated
     * @param int $formfield_id the formfield_id that the record must have
     * @return mixed object containing the plugin instance record or false
     */
    public function get_plugin_record($tablename, $formfield_id) {
        return $this->dbc->get_record($tablename, array('formfield_id' => $formfield_id));
    }

    /**
     * get the data entry record with the id given
     *
     * @param string tablename the name of the table that will be interrogated
     * @param int     $entry_id the entry id of the records that will be returned
     * @param int     $formfield_id the id of the report field
     * @param bool     $multiple is there a chance multiple records will be return
     * if yes set mutliple to true
     * @return mixed object the entry record or false
     */
    public function get_form_element_entry($tablename, $entry_id, $formfield_id, $multiple = false) {
        global $CFG;

        $entrytable = "{$CFG->prefix}{$tablename}_ent";
        $parenttable = "{$CFG->prefix}{$tablename}";

        $itemtable = (!empty($multiple)) ? "{$CFG->prefix}{$tablename}_items as i," : '';
        $where = (!empty($multiple)) ? "e.parent_id	=	i.id AND i.parent_id	=	p.id" : "e.parent_id	=	p.id";

        $sql = "SELECT		*
					 FROM 		{$parenttable} as p,
					 			{$itemtable}
					 			{$entrytable} as e
					 WHERE 		{$where}
					 AND		e.entry_id	=	{$entry_id}
					 AND		p.formfield_id	=	{$formfield_id}";

        return (empty($multiple)) ? $this->dbc->get_record_sql($sql) : $this->dbc->get_records_sql($sql);
    }

    /**
     * Create a plugin entry in the table given
     *
     * @param $tablename
     * @param $pluginentry
     * @return mixed int id of new reocrd or false
     */
    public function create_formelement_entry($tablename, $pluginentry) {
        return $this->insert_record($tablename, $pluginentry);
    }


    /** Create status element entry
     *
     */
    function create_statusfield($statusfield)	{
        $this->insert_record('ulcc_form_plg_sts', $statusfield);
    }


    /**
     * Update a plugin entry record in the table given
     *
     * @param $tablename
     * @param $pluginentry
     * @return bool true or false
     */
    public function update_formelement_entry($tablename, $pluginentry) {
        return $this->update_record($tablename, $pluginentry);
    }

    /**
     * check if any user data has been uploaded to a particular list-type reportfield
     * if it has then admin should not be allowed to delete any existing
     * options
     * @param $tablename
     * @param $formfield_id
     * @param bool $item_table
     * @param bool $item_key
     * @param bool $item_value_field
     * @internal param \tablename $string
     * @internal param \formfield_id $int
     * @internal param \item_table $string - use this item_table if item_table name is not simply $tablename . "_items"
     * @internal param \item_key $string - use this foreign key if specific item_table has been sent as arg. Send empty string to simply get all rows from the item table
     * @internal param \item_value_field $string - field from the item table to use as the value submitted to the user entry table
     * @return mixed array of objects or false
     */
    public function form_element_data_item_exists($tablename, $formfield_id, $item_table = false, $item_key = false,
                                                   $item_value_field = false) {
        global $CFG;

        $tablename = $CFG->prefix.$tablename;
        if (!$item_table) {
            $item_table = $tablename."_items";
        }
        if (false === $item_key) {
            $item_key = 'parent_id';
        }
        $entry_table = $tablename."_ent";

        $item_on_clause = '';
        if ($item_key) {
            $item_on_clause = "ON item.$item_key = ele.id";
        }

        if (!$item_value_field) {
            $item_value_field = 'value';
        }

        $sql = "SELECT *
				FROM {$tablename} ele
				JOIN {$item_table} item  $item_on_clause
				JOIN {$entry_table} entry ON entry.value = item.$item_value_field
				WHERE ele.formfield_id = {$formfield_id}
				";

        return $this->dbc->get_records_sql($sql);
    }

    /**
     * delete option items for a plugin list-type element
     *
     * @param   string $tablename     is the element table eg block_ilp_plu_category
     * @param      int $formfield_id  id of the form field whose items will deleted
     * @param    array $extraparams   containing data to put in the logs
     * @return boolean true or false
     */
    public function delete_element_listitems($tablename, $formfield_id, $extraparams = array()) {
        global $CFG;
        $real_tablename = $CFG->prefix.$tablename;
        $element_table = $tablename;
        $item_table = $tablename."_items";
        $entry_table = $tablename."_ent";

        //get parent_id
        $parent_id = $this->get_element_id_from_formfield_id($tablename, $formfield_id);

        return $this->dbc->delete_records($item_table, array('parent_id' => $parent_id), $extraparams);
    }

    /**
     * delete option items for a plugin list-type element referenced by element_id (parent_id) instead of formfield_id
     * $tablename is the element table eg block_ilp_plu_category
     * @param $tablename
     * @param $parent_id
     * @param array $extraparams
     * @internal param \tablename $string
     * @internal param \formfield_id $int
     *
     * @return boolean true or false
     */
    public function delete_element_listitems_by_parent_id($tablename, $parent_id, $extraparams = array()) {
        global $CFG;
        $real_tablename = $CFG->prefix.$tablename;
        $element_table = $tablename;
        $item_table = $tablename."_items";
        $entry_table = $tablename."_ent";

        // Get parent_id.
        //return $this->dbc->delete_records( $item_table, array( 'parent_id' => $parent_id ) , $extraparams );
        return $this->delete_records($item_table, array('parent_id' => $parent_id), $extraparams);
    }

    /**
     * Get the element id by using the form field id
     *
     * @param string    $tablename    the name of the table from which data will be retireved
     * @param int       $formfield_id the id of the form field
     *
     * @return int or false
     */
    public function get_element_id_from_formfield_id($tablename, $formfield_id) {
        $element_record = array_shift($this->dbc->get_records($tablename, array('formfield_id' => $formfield_id)));

        if (!empty($element_record)) {
            return $element_record->id;
        }
        return false;
    }

    /**
     * Returns all records from the given table that match the conditions specified
     * (this should have been made as two separate functions - despite what is written below - ND)
     *
     * the full data from listelement_item_exists is used by ilp_element_plugin_status::get_option_list(),
     * so please do not change the return type
     *
     * @param $item_tablename
     * @param array  $conditionlist array containing conditions
     * @internal param string $tablename the name of the
     * @return array of objects
     */
    public function listelement_item_exists($item_tablename, $conditionlist) {
        return $this->dbc->get_records($item_tablename, $conditionlist);
    }

    /**
     * see if an element of a particular type already exists in a form
     * @param int   $form_id the id of the form being checked
     * @param string $tablename the tablename of the element
     * @return int
     */
    public function element_type_exists($form_id, $tablename) {
        $sql = "
            SELECT COUNT( frm.id ) n
            FROM {ulcc_form_lib_form} frm
            JOIN {ulcc_form_lib_form_field} frmfd ON frmfd.form_id = frm.id
            JOIN {ulcc_form_lib_form_element} pln ON pln.id = frmfd.formelement_id
            WHERE frm.id = :form_id AND pln.tablename = :tablename
        ";

        $res = $this->dbc->get_record_sql($sql,
                                          array('tablename' => $tablename,
                                                'form_id' => $form_id));

        return $res->n;
    }

    /**
     * Returns all form field records for the occurances of the given element table
     *
     * @param int   $form_id the id of the form being checked
     * @param string $tablename the tablename of the element
     * @return array
     */
    public function element_occurances($form_id, $tablename) {
        $sql = "
            SELECT frmfd.*
            FROM {ulcc_form_lib_form} frm
            JOIN {ulcc_form_lib_form_field} frmfd ON frmfd.form_id = frm.id
            JOIN {ulcc_form_lib_form_element} pln ON pln.id = frmfd.formelement_id
            WHERE frm.id = :form_id AND pln.tablename = :tablename
        ";

        $res = $this->dbc->get_records_sql($sql,
                                           array('tablename' => $tablename,
                                                 'form_id' => $form_id));

        return $res;
    }

    /**
     * supply a formfield id for a dropdown type element
     * dropdown options are returned
     * @param int $formfield_id the id of the form field whose option list is being returned
     * @param string $tablename the name of the table
     * @param bool $field Field to get as well as id, value, name
     * @return array of objects
     */
    public function get_optionlist($formfield_id, $tablename, $field = false) {
        global $CFG;
        $tablename = $CFG->prefix.$tablename;
        $item_table = $tablename."_items";
        $plugin_table = $tablename;

        $fieldlist = array("$item_table.id",
                           'value',
                           'name');
        if ($field) {
            $fieldlist[] = $field;
        }

        $whereandlist = array(
            "$plugin_table.formfield_id = $formfield_id"
        );

        $sql = "SELECT ".implode(',', $fieldlist)."
				FROM  	{ulcc_form_lib_form_field} frmf
				JOIN 	$plugin_table ON $plugin_table.formfield_id = frmf.id
				JOIN 	$item_table ON $item_table.parent_id = $plugin_table.id
				WHERE 	$plugin_table.formfield_id = $formfield_id
		";
        return $this->dbc->get_records_sql($sql);
    }

    /**
     * check if any user data has been uploaded to a particular list-type formfield
     * if it has then admin should not be allowed to delete any existing
     * options
     * @param $tablename
     * @param $formfield_id
     * @param bool $item_table
     * @param bool $item_key
     * @param bool $item_value_field
     * @internal param \tablename $string
     * @internal param \formfield_id $int
     * @internal param \item_table $string - use this item_table if item_table name is not simply $tablename . "_items"
     * @internal param \item_key $string - use this foreign key if specific item_table has been sent as arg. Send empty string to simply get all rows from the item table
     * @internal param \item_value_field $string - field from the item table to use as the value submitted to the user entry table
     * @return mixed array of objects or false
     */
    public function plugin_data_item_exists($tablename, $formfield_id, $item_table = false, $item_key = false,
                                             $item_value_field = false) {
        global $CFG;

        $tablename = $CFG->prefix.$tablename;
        if (!$item_table) {
            $item_table = $tablename."_items";
        }
        if (false === $item_key) {
            $item_key = 'parent_id';
        }
        $entry_table = $tablename."_ent";

        $item_on_clause = '';
        if ($item_key) {
            $item_on_clause = "ON item.$item_key = ele.id";
        }

        if (!$item_value_field) {
            $item_value_field = 'value';
        }

        $sql = "SELECT *
				FROM {$tablename} ele
				JOIN {$item_table} item  $item_on_clause
				JOIN {$entry_table} entry ON entry.value = item.$item_value_field
				WHERE ele.formfield_id = {$formfield_id}
				";

        return $this->dbc->get_records_sql($sql);
    }

    /**
     * Check if the form given was created for the plugin with the name and type specified
     *
     * @param string $name  -   the name of the plugin whose forms will be returned
     * @param string $type  -   the type of the plugin that whose forms will be returned
     * @param string $formid - the id of the form being checked
     *
     * @return mixed array containing form objects or bool false
     */
    public function is_plugin_form($name, $type, $formid) {

        $sqlparams = array('name' => $name,
                           'type' => $type,
                           'formid' => $formid);

        $sql = "SELECT   f.*
                     FROM     {ulcc_form_lib_plugin}  as p,
                              {ulcc_form_lib_form}    as f
                     WHERE    p.name    =   :name
                     AND      p.type    =   :type
                     AND      p.id      =   f.plugin_id
                     AND      f.id      =   :formid
                    ";

        return $this->dbc->get_record_sql($sql, $sqlparams);
    }

    /**
     * Create a entry record
     *
     * @param object $entry the entry that you want to insert
     *
     * @return mixed int the id of the entry or false
     */
    public function create_entry($entry) {
        return $this->insert_record("ulcc_form_lib_entry", $entry);
    }

    /**
     * Updates an entry record
     *
     * @param object $entry the object that we want to update
     *
     * @return bool true or false
     */
    public function update_entry($entry) {
        return $this->update_record("ulcc_form_lib_entry", $entry);
    }

    /**
     * Returns the id of the item with the given value
     *
     * @param $tablename
     * @param $tablename
     * @param    int $parent_id    the id of the state item record that is the parent of the item
     * @param    int $itemvalue the actual value of the field
     * @param    string $keyfield field from $itemtable to use as key
     * @param bool|string $itemtable name of item table to use if this element type does not follow the '_items' naming convention
     * @param bool|string $itemtable name of item table to use if this element type does not follow the '_items' naming convention
     *
     * @return    mixed object or false
     */
    public function get_state_item_id($tablename, $parent_id, $itemvalue, $keyfield = 'id', $itemtable = false) {
        global $CFG;
    private function get_state_item_id($tablename, $parent_id, $itemvalue, $keyfield = 'id', $itemtable = false) {

        $tablename = (!empty($itemtable)) ? $itemtable : $tablename."_items";
        $params[$keyfield] = $itemvalue;

        if (!$itemtable) {
            $params['parent_id'] = $parent_id;
        }

        return $this->dbc->get_record($tablename, $params);
    }

    /**
     * Saves temp data into the ulcc_form_lib_temp table data stored using this function is serialised. It should be
     * noted that only temp data should be stored using this function as the ulcc_form_lib_temp table can be purged
     *
     * @param stdClass $data the data to be serialized and saved into the temp table
     *
     * @return mixed int the id of the data that's been saved or bool false
     */
    public function save_temp_data($data) {
        $serialiseddata = serialize($data);

        $tempdata = new stdClass();
        $tempdata->data = $serialiseddata;

        return $this->insert_record('ulcc_form_lib_temp', $tempdata);
    }

    /**
     * Updates data stored in the ulcc_form_lib_temp table
     *
     * @param int $tempid the id of the record to be updated
     * @param mixed $data the data that will be saved
     *
     * return bool true if successful false is not
     * @return mixed
     * @return bool true if successful false is not
     */
    public function update_temp_data($tempid, $data) {

        $serialiseddata = serialize($data);

        $tempdata = new stdClass();
        $tempdata->id = $tempid;
        $tempdata->data = $serialiseddata;

        return $this->update_record('ulcc_form_lib_temp', $tempdata);
    }

    /**
     * Returns the temp data with the given id
     *
     * @param int $id the id of the data that is being retrieved
     * @return mixed the data that was saved
     */
    public function get_temp_data($id) {
        $tempdata = $this->dbc->get_record('ulcc_form_lib_temp', array('id' => $id));

        return (!empty($tempdata)) ? unserialize($tempdata->data) : false;
    }

    /**
     * Deletes the temp data record with the given id
     *
     * @param int $id the id of the data that is being retrieved
     * @return mixed the data that was saved
     */
    public function delete_temp_data($id) {
        return $this->dbc->delete_records('ulcc_form_lib_temp', array('id' => $id));
    }

    /**
     * Returns the form entry with the id that matches the one given
     *
     * @param int $entry_id the id of the entry that you want to return
     *
     * @return mixed object the entry or false
     */
    public function get_form_entry($entry_id) {
        return $this->dbc->get_record('ulcc_form_lib_entry', array('id' => $entry_id));
    }

    /**
     * Returns the position number a new form should take
     *
     * @param string $type the type of the plugin that the forms will be counted for
     * @param string $name the name of the plugin that the forms will be counted for
     * @return int the new forms position number
     */
    public function get_new_form_position($type, $name) {

        $from = "FROM 		{ulcc_form_lib_form} as f,
                                {ulcc_form_lib_plugin} as p ";

        $where = "WHERE      p.name     =    :pname
                     AND        p.type     =    :ptype
                     AND        p.id       =    f.plugin_id";

        // Get a count of all the records.
        $position = $this->dbc->count_records_sql('SELECT COUNT(*) '.$from.$where,
                                                  array('pname' => $name,
                                                        'ptype' => $type));

        return (empty($position)) ? 1 : $position + 1;
    }

    /**
     * Returns the record for the form element plugin that has a name matching the one given
     *
     * @param string $elementname
     * @return mixed object form element record or bool false
     */
    public function get_form_element_by_name($elementname) {
        return $this->dbc->get_record('ulcc_form_lib_form_element', array('name' => $elementname));
    }

    /**
     * Deletes a record in the given table matching its id field
     *
     * @param   string $tablename the name of the table that the record
     * will be deleted form
     * @param    int $id the id of the record you will be deleting
     *
     * @param array $extraparams
     * @return mixed true or false
     */
    public function delete_element_record_by_id($tablename, $id, $extraparams = array()) {
        return $this->delete_records($tablename, array('id' => $id), $extraparams);
    }


    /**
     * Generic delete function used to delete items from the items table
     *
     * @param string $tablename the table that you want to delete the record from
     * @param int $parent_id the parent_id that all fields to be deleted should have
     *
     * @param array $extraparams
     * @param array $extraparams
     * @return bool true or false
     */
    public function delete_items($tablename, $parent_id, $extraparams=array() ) {
        return $this->delete_records( $tablename, array('parent_id' => $parent_id), $extraparams );
    }


    /** * Get the form element items by the id of element
     * @param string $itemtable name of the table holding items
     * @param int $id  id of the plugin element
     * @return array  of items for the given element
     */

    public function get_form_element_item_records($itemtable, $id) {
        return $this->dbc->get_records($itemtable, array('parent_id' => $id));

}


    /** Creates a new record in the _items table for the given plugin
     * @param string $itemtable  name of the table holding items
     * @param object $formitemelementrecord  duplicated item to be inserted
     * @return mixed id of new entry or false
     */
    public function create_form_element_item_record($itemtable, $formitemelementrecord) {
        return $this->insert_record($itemtable, $formitemelementrecord);
    }




}
