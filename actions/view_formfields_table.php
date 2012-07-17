<?php

/**
 * Provides a table containing all fields in the current form
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package
 * @version
 */

if (!defined('MOODLE_INTERNAL')) {
    // this must be included from a Moodle page
    die('Direct access to this script is forbidden.');
}

//include the tablelib.php file
require_once($CFG->libdir.'/tablelib.php');

// the id of the form must be provided
$form_id    =   $PARSER->required_param('form_id',PARAM_INT);

// Get the type of the plugin that is currently invoking the form library.
$moodleplugintype       =   $PARSER->required_param('moodleplugintype', PARAM_RAW);

$moodlepluginname       =   $PARSER->required_param('moodlepluginname', PARAM_RAW);


//instantiate the flextable table class
$flextable = new flexible_table("form_id{$form_id}user_id".$USER->id);

//define the base url that the table will return to
$flextable->define_baseurl($CFG->wwwroot."/blocks/ilp/actions/edit_formfields.php?".$PARSER->get_params_url());

//setup the array holding the column ids
$columns	=	array();
$columns[]	=	'label';
$columns[]	=	'type';
$columns[]	=	'moveup';
$columns[]	=	'movedown';
$columns[]	=	'edit';
$columns[]	=	'summary';
$columns[]	=	'required';
$columns[]	=	'delete';

//setup the array holding the header texts
$headers	=	array();
$headers[]	=	'';
$headers[]	=	get_string('type','local_ulcc_form_library');
$headers[]	=	'';
$headers[]	=	'';
$headers[]	=	'';
$headers[]	=	'';
$headers[]	=	'';
$headers[]	=	'';

//pass the columns to the table
$flextable->define_columns($columns);

//pass the headers to the table
$flextable->define_headers($headers);

//set the attributes of the table
$flextable->set_attribute('id', 'formfields-table');
$flextable->set_attribute('cellspacing', '0');
$flextable->set_attribute('class', 'formfieldstable flexible boxaligncenter generaltable');
$flextable->set_attribute('summary', get_string('currentfields', 'local_ulcc_form_library'));

$flextable->column_class('label', 'leftalign');

// setup the table - now we can use it
$flextable->setup();

//get the data on fields to be used in the table
$formfields		=	$dbc->get_form_fields_by_position($form_id);
$totalformfields	=	count($formfields);

$querystr   =   $PARSER->get_params_url();

if (!empty($formfields)) {
    foreach ($formfields as $row) {
        $data = array();

        $data[] 		=	$row->label;

        $plugin 		=	$dbc->get_form_element_plugin($row->formelement_id);

        //use the plugin name param to get the type field
        $plugintype		=	$plugin->name."_type";

        $data[] 		=	get_string($plugintype,'local_ulcc_form_library');

        if ($row->position != 1) {
            //if the field is in any position except 1 it needs a up icon
            $title 	=	get_string('moveup','local_ulcc_form_library');
            $icon	=	$OUTPUT->pix_url("/t/up");
            $movetype	=	"up";

            $data[] 			=	"<a href='{$CFG->wwwroot}/local/ulcc_form_library/actions/move_field.php?formfield_id={$row->id}&form_id={$form_id}&move=".FORM_MOVE_UP."&position={$row->position}&moodleplugintype={$moodleplugintype}&moodlepluginname={$moodlepluginname}'>
									<img class='move' src='{$icon}' alt='{$title}' title='{$title}' />
								 	</a>";
        } else {
            $data[] 	=	"";
        }

        if ($totalformfields != $row->position) {
            //if the field is in any position except last it needs a down icon
            $title 	=	get_string('movedown','local_ulcc_form_library');
            $icon	=	$OUTPUT->pix_url("/t/down");
            $movetype	=	"down";

            $data[] 			=	"<a href='{$CFG->wwwroot}/local/ulcc_form_library/actions/move_field.php?formfield_id={$row->id}&move=".FORM_MOVE_DOWN."&position={$row->position}&{$querystr}'>
									<img class='move' src='{$icon}' alt='{$title}' title='{$title}' />
								 	</a>";
        } else {
            $data[] 	=	"";
        }


        //set the edit field


        $data[] 			=	"<a href='{$CFG->wwwroot}/local/ulcc_form_library/actions/edit_field.php?formfield_id={$row->id}&formelement_id={$row->formelement_id}&{$querystr}'>
									<img class='edit' src='".$OUTPUT->pix_url("/i/edit")."' alt='".get_string('edit')."' title='".get_string('edit')."' />
								 </a>";

        //set the required field
        $title 	= 	(!empty($row->required)) ? get_string('required','local_ulcc_form_library') : get_string('notrequired','local_ulcc_form_library');
        $icon	= 	$CFG->wwwroot."/local/ulcc_form_library/icons/";
        $icon	.= 	(!empty($row->required)) ? "required.gif" : "notrequired.gif";

        $data[] 			=	"<a href='{$CFG->wwwroot}/local/ulcc_form_library/actions/edit_field_required.php?formfield_id={$row->id}&{$querystr}'>
									<img class='required' src='{$icon}' alt='{$title}' title='{$title}' />
								</a>";
        //set the summary row
        $title 	= 	(!empty($row->summary)) ? get_string('insummary','local_ulcc_form_library') : get_string('notinsummary','local_ulcc_form_library');
        $icon	= 	$CFG->wwwroot."/local/ulcc_form_library/icons/";
        $icon	.= 	(!empty($row->summary)) ? "summary.png" : "notinsummary.png";

        $data[] 			=	"<a href='{$CFG->wwwroot}/local/ulcc_form_library/actions/edit_field_summary.php?formfield_id={$row->id}&{$querystr}'>
									<img class='required' src='{$icon}' alt='{$title}' title='{$title}' height='16' width='16'/>
								</a>";


        $data[] 			=	"<a href='{$CFG->wwwroot}/local/ulcc_form_library/actions/delete_field.php?formfield_id={$row->id}&{$querystr}'>
									<img class='delete' src='".$OUTPUT->pix_url("/t/delete")."' alt='".get_string('delete')."' title='".get_string('delete')."' />
								 </a>";

        $flextable->add_data($data);

    }
}

require_once($CFG->dirroot.'/local/ulcc_form_library/views/view_formfields_table.html');

?>