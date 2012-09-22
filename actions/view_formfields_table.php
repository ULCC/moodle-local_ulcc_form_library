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
 * Provides a table containing all fields in the current form
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package
 * @version
 */

if (!defined('MOODLE_INTERNAL')) {
    // This must be included from a Moodle page.
    die('Direct access to this script is forbidden.');
}

global $CFG, $USER, $DB, $PARSER, $OUTPUT;

// Include the tablelib.php file.
require_once($CFG->libdir.'/tablelib.php');

// The id of the form must be provided.
$form_id = required_param('form_id', PARAM_INT);
// Get the type of the plugin that is currently invoking the form library.
$moodleplugintype = required_param('moodleplugintype', PARAM_ALPHAEXT);
$moodlepluginname = required_param('moodlepluginname', PARAM_ALPHAEXT);
$context_id = required_param('context_id', PARAM_INT);
// Instantiate the flextable table class.
$flextable = new flexible_table("form_id{$form_id}user_id".$USER->id);
// Define the base url that the table will return to.
$flextable->define_baseurl($CFG->wwwroot."/blocks/ilp/actions/edit_formfields.php?".$PARSER->get_params_url());

// Instantiate the db.
$dbc = new form_db();

// Setup the array holding the column ids.
$columns = array();
$columns[] = 'label';
$columns[] = 'type';
$columns[] = 'moveup';
$columns[] = 'movedown';
$columns[] = 'edit';
if ($moodlepluginname != 'coursework') { // Summary is not relevant to coursework, therefore is not displayed.
    $columns[] = 'summary';
}
$columns[] = 'required';
$columns[] = 'delete';

// Setup the array holding the header texts.
$headers = array();
$headers[] = '';
$headers[] = get_string('type', 'local_ulcc_form_library');
$headers[] = '';
$headers[] = '';
$headers[] = '';
if ($moodlepluginname != 'coursework') {
    $headers[] = '';
}
$headers[] = '';
$headers[] = '';

// Pass the columns to the table.
$flextable->define_columns($columns);

// Pass the headers to the table.
$flextable->define_headers($headers);

// Set the attributes of the table.
$flextable->set_attribute('id', 'formfields-table');
$flextable->set_attribute('cellspacing', '0');
$flextable->set_attribute('class', 'formfieldstable flexible boxaligncenter generaltable');
$flextable->set_attribute('summary', get_string('currentfields', 'local_ulcc_form_library'));

$flextable->column_class('label', 'leftalign');

// Setup the table - now we can use it.
$flextable->setup();

// Get the data on fields to be used in the table.
$formfields = $dbc->get_form_fields_by_position($form_id);
$totalformfields = count($formfields);

$querystr = $PARSER->get_params_url();

if (!empty($formfields)) {
    foreach ($formfields as $row) {
        $data = array();

        $data[] = $row->label;

        $plugin = $dbc->get_form_element_plugin($row->formelement_id);

        // Use the plugin name param to get the type field.
        $plugintype = $plugin->name."_type";

        $data[] = get_string($plugintype, 'local_ulcc_form_library');

        if ($row->position != 1) {
            // If the field is in any position except 1 it needs a up icon.
            $title = get_string('moveup', 'local_ulcc_form_library');
            $icon = $OUTPUT->pix_url("/t/up");
            $movetype = "up";

            $data[] = "<a href='{$CFG->wwwroot}/local/ulcc_form_library/actions/move_field.php?formfield_id={$row->id}
            &form_id={$form_id}&move=".FORM_MOVE_UP."&position={$row->position}&moodleplugintype={$moodleplugintype}
            &moodlepluginname={$moodlepluginname}&context_id={$context_id}'>
									<img class='move' src='{$icon}' alt='{$title}' title='{$title}' />
								 	</a>";
        } else {
            $data[] = "";
        }

        if ($totalformfields != $row->position) {
            // If the field is in any position except last it needs a down icon.
            $title = get_string('movedown', 'local_ulcc_form_library');
            $icon = $OUTPUT->pix_url("/t/down");
            $movetype = "down";

            $data[] = "<a href='{$CFG->wwwroot}/local/ulcc_form_library/actions/move_field.php?formfield_id={$row->id}
            &move=".FORM_MOVE_DOWN."&position={$row->position}&{$querystr}'>
									<img class='move' src='{$icon}' alt='{$title}' title='{$title}' />
								 	</a>";
        } else {
            $data[] = "";
        }

        // Set the edit field.

        $data[] = "<a href='{$CFG->wwwroot}/local/ulcc_form_library/actions/edit_field.php?formfield_id={$row->id}
        &formelement_id={$row->formelement_id}&{$querystr}'>
									<img class='edit' src='".$OUTPUT->pix_url("/i/edit")."' alt='".get_string('edit')."' title='".get_string('edit')."' />
								 </a>";

        // Set the required field.
        $title = (!empty($row->required)) ? get_string('required', 'local_ulcc_form_library') :
            get_string('notrequired', 'local_ulcc_form_library');
        $icon = $CFG->wwwroot."/local/ulcc_form_library/icons/";
        $icon .= (!empty($row->required)) ? "required.gif" : "notrequired.gif";

        $data[] = "<a href='{$CFG->wwwroot}/local/ulcc_form_library/actions/edit_field_required.php?formfield_id={$row->id}
&{$querystr}'>
									<img class='required' src='{$icon}' alt='{$title}' title='{$title}' />
								</a>";
        // Set the summary row.
        if ($moodlepluginname != 'coursework') {
            $title = (!empty($row->summary)) ? get_string('insummary', 'local_ulcc_form_library') :
                get_string('notinsummary', 'local_ulcc_form_library');
            $icon = $CFG->wwwroot."/local/ulcc_form_library/icons/";
            $icon .= (!empty($row->summary)) ? "summary.png" : "notinsummary.png";

            $data[] = "<a href='{$CFG->wwwroot}/local/ulcc_form_library/actions/edit_field_summary.php?formfield_id=
{$row->id}&{$querystr}'>
									<img class='required' src='{$icon}' alt='{$title}' title='{$title}' height='16' width='16'/>
								</a>";
        }


        $data[] = "<a href='{$CFG->wwwroot}/local/ulcc_form_library/actions/delete_field.php?formfield_id={$row->id}&{$querystr}'>
									<img class='delete' src='".$OUTPUT->pix_url("/t/delete")."' alt='".get_string('delete').
            "' title='".get_string('delete')."' />
								 </a>";

        $flextable->add_data($data);

    }
}

require_once($CFG->dirroot.'/local/ulcc_form_library/views/view_formfields_table.html');

