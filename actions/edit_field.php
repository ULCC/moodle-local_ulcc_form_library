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
 * Creates and edits a form field
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package Form library
 * @version 1.0
 */


require_once('../../../config.php');

global $USER, $CFG, $SESSION, $PARSER, $PAGE;

// Include any neccessary files.

// Meta includes.
require_once($CFG->dirroot.'/local/ulcc_form_library/action_includes.php');
// Add the breadcrumbs.
require_once($CFG->dirroot.'/local/ulcc_form_library/breadcrumbs.php');
require_once($CFG->dirroot.'/local/ulcc_form_library/lib.php');

// The id of the report  that the field will be in.
$form_id = $PARSER->required_param('form_id', PARAM_INT);
// The id of the plugin ype the field will be.
$formelement_id = $PARSER->required_param('formelement_id', PARAM_INT);
// The id of the reportfield used when editing.
$formfield_id = $PARSER->optional_param('formfield_id', null, PARAM_INT);

// Instantiate the db.
$dbc = new form_db();

// Setup the navigation breadcrumbs.

// Siteadmin or modules.

// Add section name to nav bar.
$title = (empty($formfield_id)) ? get_string('addfield', 'local_ulcc_form_library') : get_string('editfield',
    'local_ulcc_form_library');
$PAGE->navbar->add($title, null, 'title');
// Setup the page title and heading.
$SITE = $dbc->get_course_by_id(SITEID);
$PAGE->set_title($SITE->fullname." : ".$pluginname);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagetype('form-configuration');
$PAGE->set_url('/local/ulcc_form_library/actions/edit_field.php', $PARSER->get_params());
// Get the plugin record that for the plugin.
$pluginrecord = $dbc->get_formelement_by_id($formelement_id);
// Take the name field from the plugin as it will be used to call the instantiate the plugin class.
$classname = $pluginrecord->name;
// Include the class for the plugin.
include_once("{$CFG->dirroot}/local/ulcc_form_library/plugin/form_elements/{$classname}.php");

if (!class_exists($classname)) {
    print_error('noclassforplugin', 'local_ulcc_form_library', '', $pluginrecord->name);
}

// Instantiate the plugin class.
$pluginclass = new $classname();

// Has the maximum number of this field type in this report been reached?
if (!$pluginclass->can_add($form_id) && empty($formfield_id)) {
    $return_url = $CFG->wwwroot.'/local/ulcc_form_library/actions/edit_formfields.php?'.
        $PARSER->get_params_url(array('form_id', 'moodleplugintype', 'moodlepluginname', 'context_id'));
    redirect($return_url, get_string("fieldmaximum", 'local_ulcc_form_library', $pluginclass->audit_type()));
}

// Call the plugin edit function inside of which the plugin configuration mform.
$pluginclass->edit($form_id, $formelement_id, $formfield_id, $moodleplugintype, $moodlepluginname, $context_id);


require_once($CFG->dirroot.'/local/ulcc_form_library/views/edit_field.html');

?>
