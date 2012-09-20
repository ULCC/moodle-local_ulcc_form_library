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

global $USER, $CFG, $SESSION, $PARSER;

// Include any neccessary files.

// Perform access checks.
require_once($CFG->dirroot.'/local/ulcc_form_library/db/accesscheck.php');
// Meta includes.
require_once($CFG->dirroot.'/local/ulcc_form_library/action_includes.php');
// The id of the form that the field is in.

$form_id = $PARSER->required_param('form_id', PARAM_INT);
// Get the type of the plugin that is currently invoking the form library.
$moodleplugintype = $PARSER->required_param('moodleplugintype', PARAM_RAW);
$moodlepluginname = $PARSER->required_param('moodlepluginname', PARAM_RAW);
$context_id = $PARSER->required_param('context_id', PARAM_INT);

require_login();

$context = local_ulcc_form_library_get_page_context($moodleplugintype, $context_id);
$PAGE->set_context($context);

// Instantiate the db.
$dbc = new form_db();

// Get the form field record.
$form = $dbc->get_form_by_id($form_id);

if (empty($form)) {
    print_error('formnotfound', 'local_ulcc_form_library');
}

// If the report satatus is currently disabled (0) set it to enabled (1).
$res = $dbc->set_form_status($form_id, 0);
$res = $dbc->delete_form($form_id, 1);

// Save the changes to the report.
if (!empty($res)) {
    $resulttext = get_string('formdeletesuc', 'local_ulcc_form_library');
} else {
    $resulttext = get_string('formdeleteerror', 'local_ulcc_form_library');
}

$return_url = $CFG->wwwroot.'/local/ulcc_form_library/actions/view_forms.php?'.
    $PARSER->get_params_url(array('form_id', 'moodleplugintype', 'moodlepluginname', 'context_id'));
redirect($return_url, $resulttext, FORM_REDIRECT_DELAY);
