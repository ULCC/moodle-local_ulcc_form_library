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

global $DB, $PARSER, $CFG, $PAGE;

$cm_id  =   $PARSER->optional_param('cm_id',null,PARAM_INT);


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

    $PAGE->navbar->add(get_string('pluginname', 'local_ulcc_form_library'), null, 'title');

} else {

    //  Add section name to nav bar.
    $PAGE->navbar->add(get_string('administrationsite'), null, 'title');

    $PAGE->navbar->add(get_string('plugins', 'admin'), null, 'title');

    $plugintype     =   ($moodleplugintype  ==  'block')    ? get_string('blocks')  :  get_string('activitymodule') ;

    $PAGE->navbar->add($plugintype, null, 'title');

    $pluginname     =   get_string('pluginname', $moodleplugintype.'_'.$moodlepluginname);

    $PAGE->navbar->add($pluginname, null, 'title');

    $PAGE->navbar->add(get_string('pluginname', 'local_ulcc_form_library'), null, 'title');

}

