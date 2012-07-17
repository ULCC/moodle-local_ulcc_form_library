<?php

/**
 * includes files need by most form library files
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package
 * @version
 */

// Perform access checks.
require_once($CFG->dirroot.'/local/ulcc_form_library/constants.php');

// Perform access checks.
require_once($CFG->dirroot.'/local/ulcc_form_library/db/accesscheck.php');

//require the form db class
require_once($CFG->dirroot.'/local/ulcc_form_library/db/form_db.class.php');

//require the form parser class
require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_parser.class.php');

//