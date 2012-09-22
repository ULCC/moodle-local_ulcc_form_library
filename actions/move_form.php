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
 * Changes the position of a form
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package form
 * @version 1.0
 */


require_once('../../../config.php');

global $CFG, $USER, $DB, $PARSER, $PAGE;

// Perform access checks.
require_once($CFG->dirroot.'/local/ulcc_form_library/action_includes.php');
require_once($CFG->dirroot.'/local/ulcc_form_library/lib.php');

// The id of the form  that the field will be in.
$form_id = required_param('form_id', PARAM_INT);
// The id of the formfield used when editing.
$position = required_param('position', PARAM_INT);
// The id of the formfield used when editing.
$move = required_param('move', PARAM_INT);

// Instantiate the db.
$dbc = new form_db();

// Change field position.

$forms = $dbc->get_forms_by_position($position, $move);

$movesuc = true;

// Loop through fields returned.
if (!empty($forms)) {
    foreach ($forms as $r) {

        if ($r->id != $form_id) {
            // If the field is being moved up all other fields have postion value increased.
            // if the field is being moved down all other fields have postion value decreased
            // move up = 1 move down = 0.
            $newposition = (empty($move)) ? $r->position - 1 : $r->position + 1;
        } else {
            // Move the field.
            $newposition = (!empty($move)) ? $r->position - 1 : $r->position + 1;
        }

        if (!$dbc->set_new_form_position($r->id, $newposition)) $movesuc = false;
    }
} else {
    $movesuc = false;
}

$resulttext = (!empty($movesuc)) ? get_string("formmovesuc", 'local_ulcc_form_library') :
    get_string("formmoveerror", 'local_ulcc_form_library');

$return_url = $CFG->wwwroot.'/local/ulcc_form_library/actions/view_forms.php?'.
    $PARSER->get_params_url(array('moodlepluginname', 'moodleplugintype', 'context_id'));
redirect($return_url, $resulttext, FORM_REDIRECT_DELAY);

?>
