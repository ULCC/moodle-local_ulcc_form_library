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
 * includes files need by most form library files
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package
 * @version
 */

global $CFG;
// Perform access checks.
require_once($CFG->dirroot.'/local/ulcc_form_library/constants.php');

// Perform access checks.
require_once($CFG->dirroot.'/local/ulcc_form_library/db/accesscheck.php');

// Require the form db class.
require_once($CFG->dirroot.'/local/ulcc_form_library/db/form_db.class.php');

// Require the form parser class.
require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_parser.class.php');

