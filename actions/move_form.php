<?php

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

global $CFG, $USER, $DB, $PARSER;

// Perform access checks.
require_once($CFG->dirroot.'/local/ulcc_form_library/action_includes.php');


//the id of the form  that the field will be in
$form_id = $PARSER->required_param('form_id', PARAM_INT);

//the id of the formfield used when editing
$position   = $PARSER->required_param('position' ,PARAM_INT);

//the id of the formfield used when editing
$move       = $PARSER->required_param('move' ,PARAM_INT);

$moodleplugintype    =   $PARSER->required_param('moodleplugintype', PARAM_RAW);

$moodlepluginname    =   $PARSER->required_param('moodlepluginname', PARAM_RAW);

$context_id         =   $PARSER->required_param('context_id', PARAM_RAW);

// instantiate the db
$dbc = new form_db();

//change field position

$forms 	= 	$dbc->get_forms_by_position($position,$move);


$movesuc	=	true;

//loop through fields returned
if (!empty($forms)) {
    foreach($forms as $r) {

        if ($r->id != $form_id) {
            //if the field is being moved up all other fields have postion value increased
            //if the field is being moved down all other fields have postion value decreased
            //move up = 1 move down = 0
            $newposition = (empty($move)) ? $r->position-1 : $r->position+1;
        } else {
            //move the field
            $newposition = (!empty($move)) ? $r->position- 1 : $r->position+1;
        }

        if (!$dbc->set_new_form_position($r->id,$newposition)) $movesuc = false;
    }
} else {
    $movesuc	=	false;
}

$resulttext = (!empty($movesuc)) ? get_string("formmovesuc", 'local_ulcc_form_library') : get_string("formmoveerror", 'local_ulcc_form_library');

$return_url = $CFG->wwwroot.'/local/ulcc_form_library/actions/view_forms.php?' .$PARSER->get_params_url(array('moodlepluginname','moodleplugintype','context_id'));
redirect($return_url, $resulttext, FORM_REDIRECT_DELAY);

?>