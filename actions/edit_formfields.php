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
 * This page allows the various form elements to be added to a form.
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package form library
 * @version 1.0
 */

require_once('../../../config.php');

global $CFG, $USER, $SESSION, $OUTPUT, $PARSER, $PAGE;

// Perform access checks.
require_once($CFG->dirroot.'/local/ulcc_form_library/action_includes.php');
// Require the add field form.
require_once($CFG->dirroot.'/local/ulcc_form_library/classes/forms/add_field_mform.php');
// Add the breadcrumbs.
require_once($CFG->dirroot.'/local/ulcc_form_library/breadcrumbs.php');
require_once($CFG->dirroot.'/local/ulcc_form_library/lib.php');
require_once($CFG->dirroot . '/local/ulcc_form_library/classes/forms/form_entry_mform.php');

// The id of the form must be provided.
$form_id = $PARSER->required_param('form_id', PARAM_INT);
// Get the type of the plugin that is currently invoking the form library.
$moodleplugintype = $PARSER->required_param('moodleplugintype', PARAM_ALPHAEXT);
$moodlepluginname = $PARSER->required_param('moodlepluginname', PARAM_ALPHAEXT);
$context_id = $PARSER->required_param('context_id', PARAM_INT);

// Instantiate the db.
$dbc = new form_db();

$form = new form_entry_mform($form_id);

// Add section name to nav bar.

$plugintype = ($moodleplugintype == 'block') ? get_string('blocks') : get_string('activitymodule');
$pluginname = get_string('pluginname', $moodleplugintype.'_'.$moodlepluginname);
$PAGE->navbar->add(get_string('formfields', 'local_ulcc_form_library'), null, 'title');

// Setup the page title and heading.
$SITE = $dbc->get_course_by_id(SITEID);
$PAGE->set_title($SITE->fullname." : ".$pluginname);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagetype('form-configuration');
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/ulcc_form_library/actions/edit_formfields.php', $PARSER->get_params());

$fieldmform = new add_field_mform($moodlepluginname, $moodleplugintype, $context_id, $form_id);

$included = array('form_id',
                  'moodleplugintype',
                  'moodlepluginname',
                  'context_id');

// Has the form been submitted?
if ($fieldmform->is_submitted()) {
    // Get the form data submitted.
    $formdata = $fieldmform->get_data();
    $return_url = $CFG->wwwroot.'/local/ulcc_form_library/actions/edit_field.php?formelement_id='.
        $formdata->formelement_id.'&'.$PARSER->get_params_url($included);
    redirect($return_url, get_string("addfield", 'local_ulcc_form_library'), FORM_REDIRECT_DELAY);
}

$previewurl = $CFG->wwwroot.'/local/ulcc_form_library/actions/form_preview.php?'.$PARSER->get_params_url($included);

require_once($CFG->dirroot.'/local/ulcc_form_library/views/edit_formfields.html');

