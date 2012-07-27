<?php

/**
 * Perfrorms permissions checks against the user to see what they are allowed to
 * do.
 *
 * @copyright &copy; 2012 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package form
 * @version 1.0
 */

global  $CFG, $USER, $PAGE;

//the user must be logged in
require_login(0);

//we need to get the current context so we can if the user has the capababilty to use the forms library
$context_id =   required_param('context_id',PARAM_INT);

$context =   context::instance_by_id($context_id, IGNORE_MISSING);

//if there is no user context then throw an error
if (empty($context)) {
    print_error("mustspecifycontext", 'local_ulcc_form_library');
}

//make sure that the user has the ability to manipulate forms if not throw an error
if (!has_capability('local/ulcc_form_library:formadmin', $context) ) {
    print_error('not_form_admin', 'local_ulcc_form_library');
}

//TODO: we will should not be in the course context change to another context
$PAGE->set_context($context);