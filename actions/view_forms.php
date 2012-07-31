<?php

/**
 * This page displays a list of all forms that have been created in the given plugin
 * it also allows new forms to be created.
 *
 * @copyright &copy; 2012 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package form
 * @version
 */

require_once('../../../config.php');

global  $CFG, $USER, $DB, $PARSER;

// Perform access checks.
require_once($CFG->dirroot.'/local/ulcc_form_library/db/accesscheck.php');

require_once($CFG->dirroot.'/local/ulcc_form_library/db/form_db.class.php');

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin.class.php');

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_parser.class.php');

//check the plugin
require_once($CFG->dirroot.'/local/ulcc_form_library/actions/plugincheck.php');

// Instantiate the db class.
$dbc =   new form_db();

//  Add section name to nav bar.
$PAGE->navbar->add(get_string('administrationsite'), null, 'title');

$PAGE->navbar->add(get_string('plugins', 'admin'), null, 'title');

$plugintype     =   ($moodleplugintype  ==  'block')    ? get_string('blocks')  :  get_string('modules') ;

$PAGE->navbar->add($plugintype, null, 'title');

$pluginname     =   get_string('pluginname', $moodleplugintype.'_'.$moodlepluginname);

$PAGE->navbar->add($pluginname, null, 'title');

$PAGE->navbar->add(get_string('pluginname', 'local_ulcc_form_library'), null, 'title');

// Require form element plugin class so any new form elements can be installed
require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_element_plugin.class.php');

// Install new form element plugins.
form_element_plugin::install_new_plugins();

$PAGE->set_url(new moodle_url('/local/ulcc_form_library/actions/view_forms.php'));
$PAGE->set_pagelayout('admin');
// Get all forms for this plugin. that exist
$forms      =   $dbc->get_plugin_forms($moodlepluginname, $moodleplugintype);

require_once($CFG->dirroot.'/local/ulcc_form_library/views/view_forms.html');

