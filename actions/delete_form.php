<?php

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

//include any neccessary files

// Perform access checks.
require_once($CFG->dirroot.'/local/ulcc_form_library/db/accesscheck.php');

// Meta includes
require_once($CFG->dirroot.'/local/ulcc_form_library/action_includes.php');

//the id of the form that the field is in
$form_id = $PARSER->required_param('form_id', PARAM_INT);

// Get the type of the plugin that is currently invoking the form library.
$moodleplugintype       =   $PARSER->required_param('moodleplugintype', PARAM_RAW);

$moodlepluginname       =   $PARSER->required_param('moodlepluginname', PARAM_RAW);

$context_id       =   $PARSER->required_param('context_id', PARAM_INT);

// instantiate the db
$dbc = new form_db();

//get the form field record
$form		=	$dbc->get_form_by_id($form_id);

if (empty($form)) {
    print_error('formnotfound','local_ulcc_form_library');
}

//if the report satatus is currently disabled (0) set it to enabled (1)
$res = $dbc->set_form_status($form_id,0);
$res = $dbc->delete_form($form_id,1);

//save the changes to the report
if (!empty($res)) {
    $resulttext	=	get_string('formdeletesuc','local_ulcc_form_library');
} else {
    $resulttext	=	get_string('formdeleteerror','local_ulcc_form_library');
}

$return_url = $CFG->wwwroot.'/local/ulcc_form_library/actions/edit_forms.php?'.$PARSER->get_params_url(array('form_id','moodleplugintype','moodlepluginname','context_id'));
redirect($return_url, $resulttext, FORM_REDIRECT_DELAY);
