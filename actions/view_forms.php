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
 * This page displays a list of all forms that have been created in the given plugin
 * it also allows new forms to be created.
 *
 * @copyright &copy; 2012 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package form
 * @version
 */

require_once('../../../config.php');

global $CFG, $USER, $DB, $PARSER, $PAGE;

// Perform access checks.
require_once($CFG->dirroot.'/local/ulcc_form_library/db/accesscheck.php');
require_once($CFG->dirroot.'/local/ulcc_form_library/db/form_db.class.php');
require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_parser.class.php');
// Check the plugin.
require_once($CFG->dirroot.'/local/ulcc_form_library/actions/plugincheck.php');
// Add the breadcrumbs.
require_once($CFG->dirroot.'/local/ulcc_form_library/breadcrumbs.php');

// Instantiate the db class.
$dbc = new form_db();

// Require form element plugin class so any new form elements can be installed.
require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin.class.php');

// Install new form element plugins.
form_element_plugin::install_new_plugins();

$PAGE->set_url(new moodle_url('/local/ulcc_form_library/actions/view_forms.php'));
$PAGE->set_pagelayout('admin');
// Get all forms for this plugin. that exist.
$forms = $dbc->get_plugin_forms($moodlepluginname, $moodleplugintype);

$form_id = optional_param('form_id', null, PARAM_INT);
$duplicate = optional_param('duplicate', null, PARAM_INT);

// Check whether duplicate was selected.
if (!empty($form_id) && !empty($duplicate)) {
    $context_id = $PARSER->required_param('context_id', PARAM_RAW);
    $moodleplugintype = $PARSER->required_param('moodleplugintype', PARAM_RAW);
    $moodlepluginname = $PARSER->required_param('moodlepluginname', PARAM_RAW);

    // Retrieve the form to be duplicated.
    $formrecord = $dbc->get_form_by_id($form_id);

    // Duplicate the form overwriting some details.
    $formrecord->id = null; // Id will autoincrement.
    $formrecord->creatorid = $USER->id;
    $formrecord->timecreated = time();
    $formrecord->timemodified = time();

    // Get the position of the last form entered and increase it by 1.
    $latestposition = $dbc->get_form_latest_position();
    $position = $latestposition->position;

    $formrecord->position = $position + 1;

    // Insert duplicated report, return new id.
    $newid = $dbc->create_form($formrecord);


    // Retrieve fields of report that will be duplicated.
    $formfields = $dbc->get_form_fields_by_form_id($form_id);
    // Duplicate report fields.
    foreach ($formfields as $ff) {
        $ffold_id = $ff->id; // Id of old form field.
        $ff->id = null; // Id will autoincrement.
        $ff->form_id = $newid; // Point to the new id of duplicated form.
        $ff->timecreated = time();
        $ff->timemodified = time();

        // Insert duplicated field.
        $formfield_id = $dbc->create_form_field($ff);

        // Add new fields to appropriate plugin table + options of the field (if exist) to _items table
        // get the name of plugin to find a correct table.
        $pluginrecord = $dbc->get_formelement_by_id($ff->formelement_id);
        $plugintable = $pluginrecord->tablename;

        // Retrieve old element record to duplicate.
        $formelementrecord = $dbc->get_form_element_record($plugintable, $ffold_id);
        $feroldid = $formelementrecord->id; // Id of old form element record.
        // Duplicate record and insert it to the right table.
        $formelementrecord->id = null; // Autoincrement id.
        $formelementrecord->formfield_id = $formfield_id;
        $formelementrecord->timecreated = time();
        $formelementrecord->timemodified = time();

        $elementrecordid = $dbc->create_form_element_record($plugintable, $formelementrecord);

        // Get name for _items (options) table.
        $itemtable = $plugintable.'_items';

        // Check whether _items table for particular plugin exist.
        $dbman = $DB->get_manager(); // Load ddl manager.
        // Check whether  _items table exist for the current element.
        if ($dbman->table_exists($itemtable)) {

            // Retrieve items for the current element and duplicate them.
            $itemelement = $dbc->get_form_element_item_records($itemtable, $feroldid);
            foreach ($itemelement as $ie) {
                $ie->id = null; // Autoincrement id.
                $ie->parent_id = $elementrecordid;
                $ie->timecreated = time();
                $ie->timemodified = time();

                $dbc->create_form_element_item_record($itemtable, $ie);
            }
        }
    }

    $form_id = $newid; // Change form_id to id of new form.
    // redirect to edit form to make changes to thel duplicate.
    $return_url = $CFG->wwwroot.'/local/ulcc_form_library/actions/edit_form.php?form_id='.$form_id.
        '&'.$PARSER->get_params_url();
    redirect($return_url, get_string("formduplication", 'local_ulcc_form_library'), FORM_REDIRECT_DELAY);
}


require_once($CFG->dirroot.'/local/ulcc_form_library/views/view_forms.html');

