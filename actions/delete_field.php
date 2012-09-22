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
 * Deletes a report field from a report
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package
 * @version
 */

require_once('../../../config.php');

global $USER, $CFG, $SESSION, $PARSER, $PAGE;

// Include any neccessary files.

// Meta includes.
require_once($CFG->dirroot.'/local/ulcc_form_library/action_includes.php');

// The id of the form that the field is in.

$form_id = required_param('form_id', PARAM_INT);
// The id of the formfield used when editing.
$formfield_id = required_param('formfield_id', PARAM_INT);
$moodleplugintype = required_param('moodleplugintype', PARAM_ALPHAEXT);
$moodlepluginname = required_param('moodlepluginname', PARAM_ALPHAEXT);
$context_id = required_param('context_id', PARAM_INT);

// Instantiate the db.
$dbc = new form_db();

// Get the form field record.
$formfield = $dbc->get_form_field_data($formfield_id);

// Check if the report field was found.
if (!empty($formfield)) {
    // Get the plugin used for the form field.
    $formelementrecord = $dbc->get_formelement_by_id($formfield->formelement_id);
    $classname = $formelementrecord->name;
    // Include the moodle form for this table.
    include_once("{$CFG->dirroot}/local/ulcc_form_library/plugin/form_elements/{$classname}.php");

    if (!class_exists($classname)) {
        print_error('noclassforplugin', 'local_ulcc_form_library', '', $formelementrecord->name);
    }

    $pluginclass = new $classname();
    $deletedposition = $formfield->position;

    if ($pluginclass->delete_form_element($formfield_id)) {
        $resulttext = get_string('fielddeletesuc', 'local_ulcc_form_library');
        // We now need to change the positions of all fields in the report move everything under the deleted position up.
        $formfields = $dbc->get_form_fields_by_position($form_id);
        // Loop through fields returned.
        if (!empty($formfields)) {
            foreach ($formfields as $field) {

                if ($field->position > $deletedposition) {
                    // If the field is being moved up all other fields have postion value increased
                    // if the field is being moved down all other fields have postion value decreased
                    // move up = 1 move down = 0.
                    if (!$dbc->set_new_position($field->id, $field->position - 1));
                }
            }
        }
    } else {
        $resulttext = get_string('fielddeleteerror', 'local_ulcc_form_library');
    }
} else {
    $resulttext = get_string('fielddeleteerror', 'local_ulcc_form_library');
}

$return_url = new moodle_url('/local/ulcc_form_library/actions/edit_formfields.php', compact('form_id',
                                                                                             'moodleplugintype',
                                                                                             'moodlepluginname',
                                                                                             'context_id'));
redirect($return_url, $resulttext, FORM_REDIRECT_DELAY);
