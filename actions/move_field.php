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
 * Changes the position of a field within a form
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package form
 * @version 1.0
 */

require_once('../../../config.php');

global $USER, $CFG, $SESSION, $PARSER, $PAGE;

//include any neccessary files

// Meta includes
require_once($CFG->dirroot.'/local/ulcc_form_library/action_includes.php');
require_once($CFG->dirroot.'/local/ulcc_form_library/lib.php');

// The id of the form must be provided.
$form_id = $PARSER->required_param('form_id', PARAM_INT);
// Get the type of the plugin that is currently invoking the form library.
$moodleplugintype = $PARSER->required_param('moodleplugintype', PARAM_RAW);
$moodlepluginname = $PARSER->required_param('moodlepluginname', PARAM_RAW);
$context_id = $PARSER->required_param('context_id', PARAM_INT);
//the id of the formfield used when editing
$formfield_id = $PARSER->required_param('formfield_id', PARAM_INT);
//the id of the formfield used when editing
$position = $PARSER->required_param('position', PARAM_INT);
//the id of the formfield used when editing
$move = $PARSER->required_param('move', PARAM_INT);

require_login();

$context = local_ulcc_form_library_get_page_context($moodleplugintype, $context_id);
$PAGE->set_context($context);

// Instantiate the db.
$dbc = new form_db();

// Change field position.

$formfields = $dbc->get_form_fields_by_position($form_id, $position, $move);

$movesuc = true;

// Loop through fields returned.
if (!empty($formfields)) {
    foreach ($formfields as $field) {

        if ($field->id != $formfield_id) {

            // If the field is being moved up all other fields have postion value increased
            // if the field is being moved down all other fields have postion value decreased
            // move up = 1 move down = 0.

            $newposition = (empty($move)) ? $field->position - 1 : $field->position + 1;
        } else {
            // Move the field.
            $newposition = (!empty($move)) ? $field->position - 1 : $field->position + 1;
        }

        if (!$dbc->set_new_position($field->id, $newposition)) $movesuc = false;
    }
} else {
    $movesuc = false;
}

$resulttext = (!empty($movesuc)) ? get_string("changesuccess", 'local_ulcc_form_library') :
    get_string("changeerror", 'local_ulcc_form_library');

$return_url = $CFG->wwwroot."/local/ulcc_form_library/actions/edit_formfields.php?".
    $PARSER->get_params_url(array('form_id', 'moodleplugintype', 'moodlepluginname', 'context_id'));
redirect($return_url, $resulttext, FORM_REDIRECT_DELAY);


