<?php

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

// the id of the form must be provided
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

if ($moodleplugintype == CONTEXT_BLOCK) { // Plugin type is block.
    $context = context_block::instance_by_id($context_id);
} else if ($moodleplugintype == CONTEXT_MODULE) { // Plugin type is Moodle.
    $context = context_module::instance_by_id($context_id);
}
// Set context.
$PAGE->set_context($context);

// instantiate the db
$dbc = new form_db();

//change field position

$formfields = $dbc->get_form_fields_by_position($form_id, $position, $move);


$movesuc = true;

//loop through fields returned
if (!empty($formfields)) {
    foreach ($formfields as $field) {

        if ($field->id != $formfield_id) {


            //if the field is being moved up all other fields have postion value increased
            //if the field is being moved down all other fields have postion value decreased
            //move up = 1 move down = 0
            $newposition = (empty($move)) ? $field->position - 1 : $field->position + 1;
        } else {
            //move the field
            $newposition = (!empty($move)) ? $field->position - 1 : $field->position + 1;
        }

        if (!$dbc->set_new_position($field->id, $newposition)) $movesuc = false;
    }
} else {
    $movesuc = false;
}

$resulttext = (!empty($movesuc)) ? get_string("changesuccess", 'local_ulcc_form_library') : get_string("changeerror", 'local_ulcc_form_library');

$return_url = $CFG->wwwroot."/local/ulcc_form_library/actions/edit_formfields.php?".$PARSER->get_params_url(array('form_id', 'moodleplugintype', 'moodlepluginname', 'context_id'));
redirect($return_url, $resulttext, FORM_REDIRECT_DELAY);

?>
