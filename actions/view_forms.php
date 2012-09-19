<?php

/**
 * This page displays a list of all forms that have been created in the given plugin
 * it also allows new forms to be created.
 *
 * @copyright &copy; 2012 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package form
 * @version
 */

require_once('../../../config.php');
global $CFG, $USER, $DB, $PARSER, $PAGE;

// Perform access checks.
require_once($CFG->dirroot.'/local/ulcc_form_library/db/accesscheck.php');
require_once($CFG->dirroot.'/local/ulcc_form_library/db/form_db.class.php');
require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_parser.class.php');
//check the plugin
require_once($CFG->dirroot.'/local/ulcc_form_library/actions/plugincheck.php');
//add the breadcrumbs
require_once($CFG->dirroot.'/local/ulcc_form_library/breadcrumbs.php');
// Require form element plugin class so any new form elements can be installed
require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin.class.php');
require_once($CFG->dirroot.'/local/ulcc_form_library/lib.php');

$context_id = $PARSER->required_param('context_id', PARAM_RAW);
$moodleplugintype = $PARSER->required_param('moodleplugintype', PARAM_RAW);
$moodlepluginname = $PARSER->required_param('moodlepluginname', PARAM_RAW);
$form_id = optional_param('form_id', null, PARAM_INT);
$duplicate = optional_param('duplicate', null, PARAM_INT);

require_login();

set_page_context($moodleplugintype, $context_id, $context);
// Set context.
$PAGE->set_context($context);

// Instantiate the db class.
$dbc = new form_db();


// Install new form element plugins.
form_element_plugin::install_new_plugins();

$PAGE->set_url(new moodle_url('/local/ulcc_form_library/actions/view_forms.php'));
$PAGE->set_pagelayout('admin');
// Setting the page context.

// Get all forms for this plugin. that exist
$forms = $dbc->get_plugin_forms($moodlepluginname, $moodleplugintype);


//check whether duplicate was selected
if (!empty($form_id) && !empty($duplicate)) {

    //retrieve the form to be duplicated
    $formrecord = $dbc->get_form_by_id($form_id);

    //duplicate the form overwriting some details
    $formrecord->id = null; //id will autoincrement
    $formrecord->creatorid = $USER->id;
    $formrecord->timecreated = time();
    $formrecord->timemodified = time();

    //get the position of the last form entered and increase it by 1
    $latestposition = $dbc->get_form_latest_position();
    $position = $latestposition->position;

    $formrecord->position = $position + 1;

    //insert duplicated report, return new id
    $newid = $dbc->create_form($formrecord);


    //retrieve fields of report that will be duplicated
    $formfields = $dbc->get_form_fields_by_form_id($form_id);
    //duplicate report fields
    foreach ($formfields as $ff) {
        $ffold_id = $ff->id; //id of old form field
        $ff->id = null; //id will autoincrement
        $ff->form_id = $newid; //point to the new id of duplicated form
        $ff->timecreated = time();
        $ff->timemodified = time();

        //insert duplicated field
        $formfield_id = $dbc->create_form_field($ff);


        //add new fields to appropriate plugin table + options of the field (if exist) to _items table
        //get the name of plugin to find a correct table
        $pluginrecord = $dbc->get_formelement_by_id($ff->formelement_id);
        $plugintable = $pluginrecord->tablename;

        //retrieve old element record to duplicate
        $formelementrecord = $dbc->get_form_element_record($plugintable, $ffold_id);
        $feroldid = $formelementrecord->id; //id of old form element record
        //duplicate record and insert it to the right table
        $formelementrecord->id = null; //autoincrement id
        $formelementrecord->formfield_id = $formfield_id;
        $formelementrecord->timecreated = time();
        $formelementrecord->timemodified = time();

        $elementrecordid = $dbc->create_form_element_record($plugintable, $formelementrecord);

        //get name for _items (options) table
        $itemtable = $plugintable.'_items';

        //check whether _items table for particular plugin exist
        $dbman = $DB->get_manager(); //load ddl manager
        //check whether  _items table exist for the current element
        if ($dbman->table_exists($itemtable)) {

            //retrieve items for the current element and duplicate them
            $itemelement = $dbc->get_form_element_item_records($itemtable, $feroldid);
            foreach ($itemelement as $ie) {
                $ie->id = null; //autoincrement id
                $ie->parent_id = $elementrecordid;
                $ie->timecreated = time();
                $ie->timemodified = time();

                $dbc->create_form_element_item_record($itemtable, $ie);
            }
        }
    }

    $form_id = $newid; //change form_id to id of new form
    //redirect to edit form to make changes to the duplicate
    $return_url = $CFG->wwwroot.'/local/ulcc_form_library/actions/edit_form.php?form_id='.$form_id.'&'.$PARSER->get_params_url();
    redirect($return_url, get_string("formduplication", 'local_ulcc_form_library'), FORM_REDIRECT_DELAY);
}


require_once($CFG->dirroot.'/local/ulcc_form_library/views/view_forms.html');

