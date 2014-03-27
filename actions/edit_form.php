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
 * Allows a form to be created and edited
 *
 * @copyright &copy; 2012 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package form
 * @version
 */

require_once('../../../config.php');

global $CFG, $USER, $SESSION, $OUTPUT, $PARSER, $PAGE;

require_once($CFG->dirroot.'/local/ulcc_form_library/action_includes.php');
require_once($CFG->dirroot.'/local/ulcc_form_library/classes/forms/edit_form_mform.php');
// Add the breadcrumbs.
require_once($CFG->dirroot.'/local/ulcc_form_library/breadcrumbs.php');
require_once($CFG->dirroot.'/local/ulcc_form_library/lib.php');

$form_id = $PARSER->optional_param('form_id', null, PARAM_INT);
// Get the type of the plugin that is currently invoking the form library.
$moodleplugintype = $PARSER->required_param('moodleplugintype', PARAM_ALPHAEXT);
$moodlepluginname = $PARSER->required_param('moodlepluginname', PARAM_ALPHAEXT);
$context_id = $PARSER->required_param('context_id', PARAM_INT);

require_login();

$PAGE->set_url('/local/ulcc_form_library/actions/edit_form.php', $PARSER->get_params());

$dbc = new form_db();

$mform = new edit_form_mform($moodlepluginname, $moodleplugintype, $context_id, $form_id);

// Was the form cancelled?
if ($mform->is_cancelled()) {
    // Send the user back.
    $return_url = $CFG->wwwroot.'/local/ulcc_form_library/actions/view_forms.php?'.$PARSER->get_params_url();
    redirect($return_url, '', FORM_REDIRECT_DELAY);
}

// Was the form submitted?
// has the form been submitted?
if ($mform->is_submitted()) {
    // Check the validation rules.
    if ($mform->is_validated()) {

        // Get the form data submitted.
        $formdata = $mform->get_data();

        // Only try to change the icon if a file was submitted.
        if ($mform->get_file_content('binary_icon') != false) {
            $formdata->binary_icon = $mform->get_file_content('binary_icon');
        } else {
            $formdata->binary_icon = '';
        }

        // Process the data.
        $success = $mform->process_data($formdata);

        // If saving the data was not successful.
        if (!$success) {
            // Print an error message.
            print_error(get_string("formcreationerror", 'local_ulcc_form_library'), 'local_ulcc_form_library');
        }

        // If the report_id has not already been set.
        $form_id = (empty($form_id)) ? $success : $form_id;

        // Decide whether the user has chosen to save and exit or save or display.
        if (isset($formdata->saveanddisplaybutton)) {
            $return_url = $CFG->wwwroot.'/local/ulcc_form_library/actions/view_forms.php?'.$PARSER->get_params_url();
            redirect($return_url, get_string("formcreation", 'local_ulcc_form_library'), FORM_REDIRECT_DELAY);
        }
    }
}

// Set the page title.
$pagetitle = (empty($form_id)) ? get_string('createform', 'local_ulcc_form_library') :
    get_string('editform', 'local_ulcc_form_library');

if (!empty($form_id)) {
    $formrecord = $dbc->get_form_by_id($form_id);
    $editortext = $formrecord->description;
    $formrecord->description = array(
        'text' => $editortext, 'format' => FORMAT_MOODLE
    );
    $mform->set_data($formrecord);
}

// Add create form title to nav.
$PAGE->navbar->add(get_string('createform', 'local_ulcc_form_library'), null, 'title');

// Setup the page title and heading.
$SITE = $dbc->get_course_by_id(SITEID);
$pluginname = get_string('pluginname', $moodleplugintype.'_'.$moodlepluginname);
$PAGE->set_title($SITE->fullname." : ".$pluginname);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagetype('form-configuration');
$PAGE->set_pagelayout('admin');


require_once($CFG->dirroot.'/local/ulcc_form_library/views/edit_form.html');


