<?php
/**
 * Creates and displays a table with all forms for the current module or block
 *
 * @copyright &copy; 2012 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package form
 * @version 1.0
 */

if (!defined('MOODLE_INTERNAL')) {
    // this must be included from a Moodle page
    die('Direct access to this script is forbidden.');
}

global  $CFG, $USER, $DB, $PARSER;

require_once($CFG->libdir.'/tablelib.php');

$moodleplugintype       =   $PARSER->required_param('moodleplugintype', PARAM_RAW);

$moodlepluginname       =   $PARSER->required_param('moodlepluginname', PARAM_RAW);

$context_id             =   $PARSER->required_param('context_id', PARAM_RAW);

// Create the filed table.

// Instantiate the flex table class.ADODB_Exception.
$flextable  =   new flexible_table('pluginforms');

// Define the base url that the table will return to.
$flextable->define_baseurl($CFG->wwwroot.'/local/ulcc_form_library/actions/view_forms.php');

// Setup the array holding the column ids
$columns	=	array();
$columns[]	=	'formname';
$columns[]	=	'moveup';
$columns[]	=	'movedown';
$columns[]  =   'duplicateform';
$columns[]	=	'editform';
$columns[]	=	'editfields';
$columns[]	=	'changestatus';
$columns[]	=	'delete';

//  Setup the array holding the header texts.
$headers	=	array();
$headers[]  =   '';
$headers[]  =   '';
$headers[]  =   '';
$headers[]  =   '';
$headers[]  =   '';
$headers[]  =   '';
$headers[]  =   '';
$headers[]  =   '';


// Pass the columns to the table.
$flextable->define_columns($columns);

// Pass the headers to the table.
$flextable->define_headers($headers);

// Tell the table it is not sortable.
$flextable->sortable(false);

// Set the attributes of the table.
$flextable->set_attribute('id', 'formfields-table');
$flextable->set_attribute('cellspacing', '0');
$flextable->set_attribute('class', 'formfieldstable flexible boxaligncenter generaltable');
$flextable->set_attribute('summary', get_string('formfields', 'local_ulcc_form_library'));

$flextable->column_class('label', 'leftalign');

// Setup the table - now we can use it.
$flextable->setup();

$querystr   =   $PARSER->get_params_url();

// Get the data on fields to be used in the table.
$forms		=	$dbc->get_forms_table($flextable, $moodlepluginname, $moodleplugintype);
$totalformfields	=	count($forms);

if (!empty($forms))   {
    foreach($forms  as $row)   {
        $data   =   array();

        $data[] =   $row->name;

        if ($row->position != 1) {
            // If the field is in any position except 1 it needs a up icon.
            $title 	=	get_string('moveup','local_ulcc_form_library');
            $icon	=	$OUTPUT->pix_url("/t/up");
            $movetype	=	"up";

            $data[] 			=	"<a href='{$CFG->wwwroot}/local/ulcc_form_library/actions/move_form.php?form_id={$row->id}&move=".FORM_MOVE_UP."&position={$row->position}&{$querystr}
            '>
                                        <img class='move' src='{$icon}' alt='{$title}' title='{$title}' />
                                    </a>";
        } else {
            $data[] 	=	"";
        }

        if ($totalformfields != $row->position) {
            // if the field is in any position except last it needs a down icon.
            $title 	=	get_string('movedown','local_ulcc_form_library');
            $icon	=	$OUTPUT->pix_url("/t/down");
            $movetype	=	"down";

            $data[] 			=	"<a href='{$CFG->wwwroot}/local/ulcc_form_library/actions/move_form.php?form_id={$row->id}&move=".FORM_MOVE_DOWN."&position={$row->position}&{$querystr}'>
                                    <img class='move' src='{$icon}' alt='{$title}' title='{$title}' />
                                    </a>";
        } else {
            $data[] 	=	"";
        }


        // Set the duplicate form link.
        $data[] 		=	"<a href='{$CFG->wwwroot}/local/ulcc_form_library/actions/view_forms.php?form_id={$row->id}&{$querystr}&duplicate=1'>
                                    <img class='edit' src='".$OUTPUT->pix_url("/t/copy")."' alt='".get_string('duplicate')."' title='".get_string('duplicate')."' />
                                 </a>";



        // Set the edit form link.
        $data[] 		=	"<a href='{$CFG->wwwroot}/local/ulcc_form_library/actions/edit_form.php?form_id={$row->id}&{$querystr}'>
                                    <img class='edit' src='".$OUTPUT->pix_url("/i/edit")."' alt='".get_string('edit')."' title='".get_string('edit')."' />
                                 </a>";

        // Set the edit form fields link.
        $data[] 		=	"<a href='{$CFG->wwwroot}/local/ulcc_form_library/actions/edit_formfields.php?form_id={$row->id}&{$querystr}'>
                                    <img class='prompt' src='".$OUTPUT->pix_url('i/questions')."' alt='".get_string('editfields','local_ulcc_form_library')."' title='".get_string('editfields','local_ulcc_form_library')."' />
                                 </a>";

        // Decide whether the form is enabled or disabled and set the image and link accordingly.
        $title 			= 	(!empty($row->status)) ? get_string('disableform','local_ulcc_form_library')  : get_string('enableform','local_ulcc_form_library');

        $icon	= 	(!empty($row->status)) ? "hide" : "show";

        $data[] 		=	"<a href='{$CFG->wwwroot}/local/ulcc_form_library/actions/edit_form_status.php?form_id={$row->id}&{$querystr}'>
                                    <img class='status' src=".$OUTPUT->pix_url("/i/".$icon)." alt='".$title."' title='".$title."' />
                            </a>";


        // Set the delete field this is not enabled at the moment.
        $data[] 			=	"<a href='{$CFG->wwwroot}/local/ulcc_form_library/actions/delete_form.php?form_id={$row->id}&{$querystr}'>
                                    <img class='delete' src='".$OUTPUT->pix_url("/t/delete")."' alt='".get_string('delete')."' title='".get_string('delete')."' />
                                 </a>";


        $flextable->add_data($data);

    }

}

require_once($CFG->dirroot.FORM_LIB_VIEWS_PATH.'/view_forms_table.html');