<?php

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
// require action_includes.php
require_once($CFG->dirroot.'/local/ulcc_form_library/action_includes.php');
require_once($CFG->dirroot.'/local/ulcc_form_library/lib.php');

$moodleplugintype = $PARSER->required_param('moodleplugintype', PARAM_RAW);
$moodlepluginname = $PARSER->required_param('moodlepluginname', PARAM_RAW);
$context_id = $PARSER->required_param('context_id', PARAM_INT);
$form_id = $PARSER->required_param('form_id', PARAM_RAW);

require_login();

// Set context.
$PAGE->set_context($context);

// instantiate the db
$dbc = new form_db();

//get the form
$form = $dbc->get_form_by_id($form_id);

//if the form is not found throw an error
if (empty($form)) {
    print_error('formnotfouund', 'local_ulcc_form_library');
}

//if the form satatus is currently disabled (0) set it to enabled (1)
if (empty($form->status)) {
    $res = $dbc->set_form_status($form_id, 1);
} else {
    $res = $dbc->set_form_status($form_id, 0);
}

//save the changes to the form
if (!empty($res)) {
    $resulttext = get_string('changesuccess', 'local_ulcc_form_library');
} else {
    $resulttext = get_string('changeerror', 'local_ulcc_form_library');
}

$return_url = $CFG->wwwroot."/local/ulcc_form_library/actions/view_forms.php?".$PARSER->get_params_url();
redirect($return_url, $resulttext, FORM_REDIRECT_DELAY);