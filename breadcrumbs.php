<?php

/**
 *
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package
 * @version
 */

global $DB, $PARSER, $CFG, $PAGE, $OUTPUT;

$cm_id  =   $PARSER->optional_param('cm_id',null,PARAM_INT);


//if the cm_id is empty check the global cfg to see if it has been saved into it
if (empty($cm_id))  {
    $cm_id  =   (isset($CFG->ulcc_form_lib['cm_id']))  ? $CFG->ulcc_form_lib['cm_id']  : null ;
} else {
    if (!isset($CFG->ulcc_form_lib))    $CFG->ulcc_form_lib     =   array();
    $CFG->ulcc_form_lib['cm_id']    =   $cm_id;
}


// Get the type of the plugin that is currently invoking the form library.
$moodleplugintype    =   optional_param('moodleplugintype', false, PARAM_RAW);

$moodlepluginname   =   optional_param('moodlepluginname', false, PARAM_RAW);

$context_id         =   required_param('context_id', PARAM_INT);

$pluginname         =   get_string('pluginname', $moodleplugintype.'_'.$moodlepluginname);

$formpagelink       = new moodle_url('/local/ulcc_form_library/actions/view_forms.php',
                      array('moodleplugintype' => $moodleplugintype, 'moodlepluginname' => $moodlepluginname,
                            'context_id' => $context_id));

if (isset($cm_id))    {
    //set the nav bar -> courses -> course -> coursemodule -> form lib

    $coursemodule = get_coursemodule_from_id('coursework', $cm_id, 0,
        false, MUST_EXIST);

    //  Add courses
    $PAGE->navbar->add(get_string('courses'), null, 'title');

    $course     =   $DB->get_record('course',array('id'=>$coursemodule->course));

    $courselink = new moodle_url('/course/view.php', array('id' => $course->id));

    //  Add course name to the nav bar
    $PAGE->navbar->add($course->shortname, $courselink, 'title');

    $cmlink = new moodle_url('/mod/coursework/view.php', array('id' => $cm_id));

    //  Add course name to the nav bar
    $PAGE->navbar->add($coursemodule->name, $cmlink, 'title');

    $PAGE->navbar->add(get_string('pluginname', 'local_ulcc_form_library'), $formpagelink, 'title');
} else {
    //  Add section name to nav bar.
    $PAGE->navbar->add(get_string('administrationsite'), null, 'title');

    $PAGE->navbar->add(get_string('plugins', 'admin'), null, 'title');

    $plugintype     =   ($moodleplugintype  ==  'block')    ? get_string('blocks')  :  get_string('activitymodule') ;

    $PAGE->navbar->add($plugintype, null, 'title');

    $PAGE->navbar->add($pluginname, null, 'title');

    $PAGE->navbar->add(get_string('pluginname', 'local_ulcc_form_library'), $formpagelink, 'title');
}

