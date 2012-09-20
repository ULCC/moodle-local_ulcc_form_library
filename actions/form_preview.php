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
 * Displays a preview of the form with the given id
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package Form Library
 * @version 1.0
 */

require_once('../../../config.php');

global $USER, $CFG, $SESSION, $PARSER, $PAGE;

// Include any neccessary files.

// Meta includes.
require_once($CFG->dirroot.'/local/ulcc_form_library/action_includes.php');
// Include the report entry preview mform class.
require_once($CFG->dirroot.'/local/ulcc_form_library/classes/forms/form_preview_mform.php');
// Add the breadcrumbs.
require_once($CFG->dirroot.'/local/ulcc_form_library/breadcrumbs.php');
require_once($CFG->dirroot.'/local/ulcc_form_library/lib.php');

// The id of the report  that the field will be in.
$form_id = $PARSER->required_param('form_id', PARAM_INT);
// Get the type of the plugin that is currently invoking the form library.
$moodleplugintype = $PARSER->required_param('moodleplugintype', PARAM_RAW);
$moodlepluginname = $PARSER->required_param('moodlepluginname', PARAM_RAW);
$context_id = $PARSER->required_param('context_id', PARAM_INT);

require_login();

$context = local_ulcc_form_library_get_page_context($moodleplugintype, $context_id);
// Set context.
$PAGE->set_context($context);

// Instantiate the db.
$dbc = new form_db();

// Setup the navigation breadcrumbs.

// Siteadmin or modules.

// Add section name to nav bar.
$PAGE->navbar->add(get_string('formpreview', 'local_ulcc_form_library'), null, 'title');


// Setup the page title and heading.
$SITE = $dbc->get_course_by_id(SITEID);
$PAGE->set_title($SITE->fullname." : ".$pluginname);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagetype('form-configuration');
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/ulcc_form_library/actions/edit_field.php', $PARSER->get_params());

$mform = new    form_preview_mform($form_id, $moodleplugintype, $moodlepluginname, $context_id);

$previewform = $dbc->get_form_by_id($form_id);

require_once($CFG->dirroot.'/local/ulcc_form_library/views/form_preview.html');
