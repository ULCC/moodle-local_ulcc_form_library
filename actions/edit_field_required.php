<?php

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

global $USER, $CFG, $SESSION, $PARSER;

//include any neccessary files

// Perform access checks.
require_once($CFG->dirroot.'/local/ulcc_form_library/db/accesscheck.php');

// Meta includes
require_once($CFG->dirroot.'/local/ulcc_form_library/action_includes.php');

//the id of the report  that the field will be in
$form_id = $PARSER->required_param('form_id', PARAM_INT);

//the id of the formfield used when editing
$formfield_id = $PARSER->required_param('formfield_id' ,PARAM_INT);

// Get the type of the plugin that is currently invoking the form library.
$moodleplugintype       =   $PARSER->required_param('moodleplugintype', PARAM_RAW);

$moodlepluginname       =   $PARSER->required_param('moodlepluginname', PARAM_RAW);

$context_id             =   $PARSER->required_param('context_id', PARAM_RAW);

// instantiate the db
$dbc = new form_db();

//change field required


//get the field record
$formfield =	$dbc->get_form_field_data($formfield_id);

//if the report field is currently required set it to 0 not required and vice versa
$formfield->required	=	(empty($formfield->required)) ? 1 : 0;

$resulttext = ($dbc->update_form_field($formfield)) ? get_string("fieldreqsuc", 'block_ilp') : get_string("fieldreqerror", 'block_ilp');

$return_url = $CFG->wwwroot.'/local/ulcc_form_library/actions/edit_formfields.php?'.$PARSER->get_params_url(array('form_id','moodleplugintype','moodlepluginname'));
redirect($return_url, $resulttext, FORM_REDIRECT_DELAY);

?>