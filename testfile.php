<?php
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


$sitecontext	=	context_system::instance();
$PAGE->set_context($sitecontext);
$PAGE->set_title('test');
$PAGE->set_heading('test');
$PAGE->set_pagetype('ilp-entry');
$PAGE->set_url($CFG->wwwroot."/blocks/local/ulcc_form_library/testfile.php");



// initialise the js for the page
//$PAGE->requires->js_init_call('M.core_filepicker',array(),true);




$uf =   new ulcc_form('block','tags');

$pageurl    =   $CFG->wwwroot.'/local/ulcc_form_library/testfile.php';
$cancelurl    =   $CFG->wwwroot.'/local/ulcc_form_library/testfile.php?type=cancel';
$processurl    =   $CFG->wwwroot.'/local/ulcc_form_library/testfile.php?type=process';

echo $OUTPUT->header();



$test = get_plugin_config('block','tags');
$entry_id   =   $uf->create_form_entry(3,2);

$test   = $uf->display_form_entry($entry_id);


$uf->set_form_element_entry_value($entry_id,'form_element_plugin_text','test data');

$test   = $uf->display_form_entry($entry_id);

/*
 *
 *
 *
$test   =   $uf->display_form(4,$pageurl,$cancelurl,$processurl);
//var_dump($test);
//$test   =   $uf->display_form_entry(29);

*/


echo $OUTPUT->footer();