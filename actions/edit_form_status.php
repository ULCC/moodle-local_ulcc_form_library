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
 *  Changes the status of a form
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package
 * @version
 */

require_once('../../../config.php');

global $CFG, $USER, $DB, $PARSER, $PAGE;

// Perform access checks.
require_once($CFG->dirroot.'/local/ulcc_form_library/db/accesscheck.php');
// Require action_includes.php.
require_once($CFG->dirroot.'/local/ulcc_form_library/action_includes.php');
require_once($CFG->dirroot.'/local/ulcc_form_library/lib.php');

$moodleplugintype = $PARSER->required_param('moodleplugintype', PARAM_RAW);
$moodlepluginname = $PARSER->required_param('moodlepluginname', PARAM_RAW);
$context_id = $PARSER->required_param('context_id', PARAM_INT);
$form_id = $PARSER->required_param('form_id', PARAM_RAW);

require_login();

$context = local_ulcc_form_library_get_page_context($moodleplugintype, $context_id);
// Set context.
$PAGE->set_context($context);

// instantiate the db
$dbc = new form_db();

// Get the form.
$form = $dbc->get_form_by_id($form_id);

// If the form is not found throw an error.
if (empty($form)) {
    print_error('formnotfouund', 'local_ulcc_form_library');
}

// If the form satatus is currently disabled (0) set it to enabled (1).
if (empty($form->status)) {
    $res = $dbc->set_form_status($form_id, 1);
} else {
    $res = $dbc->set_form_status($form_id, 0);
}

// Save the changes to the form.
if (!empty($res)) {
    $resulttext = get_string('changesuccess', 'local_ulcc_form_library');
} else {
    $resulttext = get_string('changeerror', 'local_ulcc_form_library');
}

$return_url = $CFG->wwwroot."/local/ulcc_form_library/actions/view_forms.php?".$PARSER->get_params_url();
redirect($return_url, $resulttext, FORM_REDIRECT_DELAY);
