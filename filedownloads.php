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
 * Handles downloads for form elements
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package form_library
 * @version 1.0
 *
 */

require_once('../../config.php');
require_once('../../lib/filelib.php');
require_once('lib.php');

$relativepath = get_file_argument();
$forcedownload = optional_param('forcedownload', 0, PARAM_BOOL);


// Extract relative path components.
$args = explode('/', ltrim($relativepath, '/'));

if (count($args) < 3) { // Always at least context, component and filearea.
    print_error('invalidarguments');
}

$contextid = (int)array_shift($args);
$component = clean_param(array_shift($args), PARAM_COMPONENT);
$filearea = clean_param(array_shift($args), PARAM_AREA);

list($context, $course, $cm) = get_context_info_array($contextid);

ulcc_form_library_pluginfile($context, $filearea, $args, $forcedownload);