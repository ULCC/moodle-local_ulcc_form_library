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
 * Changes the required of a field in a report
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package Form library
 * @version 1.0
 */

require_once('../../../config.php');

global $USER, $CFG, $SESSION, $PARSER, $PAGE;

// Include any neccessary files.

// Perform access checks.
require_once($CFG->dirroot.'/local/ulcc_form_library/db/accesscheck.php');
// Meta includes
require_once($CFG->dirroot.'/local/ulcc_form_library/action_includes.php');
require_once($CFG->dirroot.'/local/ulcc_form_library/lib.php');

// The id of the report  that the field will be in.
$form_id = $PARSER->required_param('form_id', PARAM_INT);
// The id of the formfield used when editing.
$formfield_id = $PARSER->required_param('formfield_id', PARAM_INT);
// Get the type of the plugin that is currently invoking the form library.
$moodleplugintype = $PARSER->required_param('moodleplugintype', PARAM_RAW);
$moodlepluginname = $PARSER->required_param('moodlepluginname', PARAM_RAW);
$context_id = $PARSER->required_param('context_id', PARAM_RAW);

// Instantiate the db.
$dbc = new form_db();

// Change field required.

// Get the field record.
$formfield = $dbc->get_form_field_data($formfield_id);

// If the report field is currently required set it to 0 not required and vice versa.
$formfield->required = (empty($formfield->required)) ? 1 : 0;

$resulttext = ($dbc->update_form_field($formfield)) ? get_string("fieldreqsuc", 'local_ulcc_form_library') :
    get_string("fieldreqerror", 'local_ulcc_form_library');

$return_url = $CFG->wwwroot.'/local/ulcc_form_library/actions/edit_formfields.php?'.
    $PARSER->get_params_url(array('form_id', 'moodleplugintype', 'moodlepluginname', 'context_id'));
redirect($return_url, $resulttext, FORM_REDIRECT_DELAY);

?>
