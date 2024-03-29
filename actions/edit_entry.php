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
 * Creates a entry for a form
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package Form Library
 * @version 1.0
 */

require_once('../../../config.php');

global $USER, $CFG, $SESSION, $PARSER, $PAGE;

// Meta includes.
require_once($CFG->dirroot.'/local/ulcc_form_library/action_includes.php');
// Add the breadcrumbs.
require_once($CFG->dirroot.'/local/ulcc_form_library/breadcrumbs.php');
require_once($CFG->dirroot.'/local/ulcc_form_library/lib.php');

// The id of the report  that the field will be in.
$form_id = $PARSER->required_param('form_id', PARAM_INT);
$moodleplugintype = $PARSER->required_param('moodleplugintype', PARAM_ALPHAEXT);
$moodlepluginname = $PARSER->required_param('moodlepluginname', PARAM_ALPHAEXT);
$context_id = $PARSER->required_param('context_id', PARAM_INT);

// Instantiate the db.
$dbc = new form_db();

// Setup the navigation breadcrumbs.

// Siteadmin or modules.
$PAGE->navbar->add(get_string('formpreview', 'local_ulcc_form_library'), null, 'title');

// Setup the page title and heading.
$SITE = $dbc->get_course_by_id(SITEID);
$pluginname = get_string('pluginname', $moodleplugintype.'_'.$moodlepluginname);
$PAGE->set_title($SITE->fullname." : ".$pluginname);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagetype('form-configuration');
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/ulcc_form_library/actions/edit_field.php', $PARSER->get_params());
