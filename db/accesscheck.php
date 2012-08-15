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
 * Performs permissions checks against the user to see what they are allowed to
 * do.
 *
 * @copyright &copy; 2012 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package form
 * @version 1.0
 */

global  $CFG, $DB, $USER, $PAGE;

$context_id = optional_param('context_id', 0, PARAM_INT);
$context = false;
if ($context_id) {
    // We need to get the current context so we can if the user has the capability to use the forms library.
    $context = context::instance_by_id($context_id, IGNORE_MISSING);
}

if ($context && $context->contextlevel == CONTEXT_MODULE) {
    $coursemodule = $DB->get_record('course_modules', array('id' => $context->instanceid));
    $course = $DB->get_record('course', array('id' => $coursemodule->course));
    require_login($course, false, $coursemodule);
} else {
    // The user must be logged in.
    require_login(0);
    $context = context_system::instance();
}

// If there is no user context then throw an error.
if (empty($context)) {
    print_error("mustspecifycontext", 'local_ulcc_form_library');
}

// Make sure that the user has the ability to manipulate forms if not throw an error.
if (!has_capability('local/ulcc_form_library:formadmin', $context) ) {
    print_error('not_form_admin', 'local_ulcc_form_library');
}
