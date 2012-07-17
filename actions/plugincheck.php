<?php

/**
 * Checks that the plugin details specified are for an actual Moodle plugin.
 * Also creates a plugin id in the form library plugin table if the Moodle
 * plugin does not already exist
 *
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package form library
 * @version 1.0
 */


// Get the type of the plugin that is currently invoking the form library.
$moodleplugintype    =   optional_param('moodleplugintype', false, PARAM_RAW);

$moodlepluginname   =   optional_param('moodlepluginname', false, PARAM_RAW);

// Instantiate the db class.
$dbc =   new form_db();

/*
if either the plugin name or type has not been supplied we have to send the user to
the plugin select page so they can select the plugin they will be making or editing forms
for
*/

if (empty($moodlepluginname) || empty($moodleplugintype))   {
    //redirect user to plugin select
    redirect('no_plugin_selected', get_string('selectplugin', 'local_ulcc_form_library'), REDIRECT_DELAY);
} else {

    // Lets check if the plugin has been created in the form library plugin table.
    $moodleplugin       =   $dbc->get_moodle_plugin($moodlepluginname, $moodleplugintype);
    if (empty($moodleplugin))   {
        // The plugin does not exist lets check if it is valid and if yes create it
        $pluginexists  =   ($moodleplugintype  ==  'block')    ?   $dbc->get_block_by_name($moodlepluginname)
            :   $dbc->get_mod_by_name($moodlepluginname);


        if (!empty($pluginexists))  {
            // The plugin was found so we can create a record for it.
            $formpluginrecord           =   new stdClass();
            $formpluginrecord->name     =   $moodlepluginname;
            $formpluginrecord->type     =   $moodleplugintype;

            // Create the form plugin
            $dbc->create_plugin($formpluginrecord);
        }   else    {
            print_error('moodlepluginnotfound');
        }
    }
}