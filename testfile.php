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
 * Created by JetBrains PhpStorm.
 * User: Nigel.Daley
 * Date: 04/07/12
 * Time: 11:48
 * To change this template use File | Settings | File Templates.
 */

require_once('../../config.php');

global $CFG, $PAGE, $OUTPUT;

require_once($CFG->dirroot.'/local/ulcc_form_library/ulcc_form.class.php');
require_once($CFG->dirroot.'/local/ulcc_form_library/lib.php');

$sitecontext = context_system::instance();
$PAGE->set_context($sitecontext);
$PAGE->set_title('test');
$PAGE->set_heading('test');
$PAGE->set_pagetype('ilp-entry');
$PAGE->set_url($CFG->wwwroot."/blocks/local/ulcc_form_library/testfile.php");


// Initialise the js for the page
// $PAGE->requires->js_init_call('M.core_filepicker',array(),true);.


$uf = new ulcc_form('block', 'tags');

$pageurl = $CFG->wwwroot.'/local/ulcc_form_library/testfile.php';
$cancelurl = $CFG->wwwroot.'/local/ulcc_form_library/testfile.php?type=cancel';
$processurl = $CFG->wwwroot.'/local/ulcc_form_library/testfile.php?type=process';

echo $OUTPUT->header();


$test = get_plugin_config('block', 'tags');
$entry_id = $uf->create_form_entry(3, 2);

$test = $uf->display_form_entry($entry_id);


$uf->set_form_element_entry_value($entry_id, 'form_element_plugin_text', 'test data');

$test = $uf->display_form_entry($entry_id);

/* ...
$test   =   $uf->display_form(4,$pageurl,$cancelurl,$processurl);
var_dump($test);
$test   =   $uf->display_form_entry(29);...
*/


echo $OUTPUT->footer();