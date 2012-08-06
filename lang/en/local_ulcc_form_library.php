<?php
/**
 * Local ulcc web services language files
 *
 * @copyright &copy; 2011 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package ULCC_webservices
 * @version 1.0
 */


// A
$string['addfield'] 			= 	'Add Field';
$string['addtosummary'] 		= 	'Add to summary';



// B
$string['binary_icon']          = 'Icon File';
$string['blockname']            = 'ULCC Form Library';


// C
$string['createnewform']        = 'Create new form';
$string['createform']           = 'Create Form';
$string['changesuccess']        = 'The change was sucessfull';
$string['changeerror']           = 'An error occurred the change was not applied, please retry';
$string['currentfields']         = 'Current fields';


// D
$string['description']          = 'Description';
$string['disableform']          = 'Disable form';



// E
$string['enableform']               = 'Enable form';
$string['editfields']               = 'Edit fields';
$string['editfield']                = 'Edit field';
$string['editform']                 = 'Edit form';
$string['entrynotfound']            = 'Entry not found';



// F
$string['formadmin']        = 'Form administration';
$string['formadmindesc']    = 'From this section you can create, edit and delete forms';
$string['formfields']       = 'Form fields';
$string['forms']            = 'Forms';
$string['formcreation']     = 'Form created';
$string['formpreview']     = 'Form preview';
$string['formmovesuc']      = 'Form successfully moved';
$string['formmoveerror']    = 'A error occured whilst changing the form position';
$string['fieldcreationsuc']		=	'The field was successfully created';
$string['fieldcreationerror']		=	'An error has occurred the field was not created';
$string['fielddeletesuc']		=	'The field was successfully deleted';
$string['fielddeleteerror']		=	'An error has occurred the field was not deleted';
$string['fieldreqsuc']		    =	'The change to the field has been successfully applied';
$string['formdeletesuc']		=	'The form was successfully deleted';
$string['formdeleteerror']		=	'An error has occurred the form was not deleted';


// I
$string['identifier']      = 'Identifier';
$string['insummary']        = 'In summary';

// L

$string['label']            = 'Label';

//M
$string['moveup']            = 'Move up';
$string['movedown']          = 'Move down';


// N
$string['name']            = 'Name';
$string['next']            = 'Next';
$string['notrequired']     = 'Not required';
$string['notinsummary']     = 'Not in summary';



// P
$string['pluginname']   = 'ULCC Form Library';
$string['plugintype']   = 'Plugin type';
$string['previewform']   = 'Preview Form';
$string['previewdescription']   = "Below is a preview of the form";
$string['previous']            = 'Previous';


// R
$string['reportconfiguration']    = 'Form Configuration';
$string['required']               = 'Required';
$string['req']                  = 'Required';
$string['reportcreationsuc']    = 'Report successfully created';



// S
$string['selectplugin']     = 'A plugin must be selected';

// T
$string['type']             = 'Type';

// V
$string['viewformpreview']  = 'View form preview';


global $CFG;

// Include lib.php file
require_once($CFG->dirroot.'/local/ulcc_form_library/lib.php');

// Include form db class
require_once($CFG->dirroot.'/local/ulcc_form_library/db/form_db.class.php');

$dbc = new form_db();
$plugins = $CFG->dirroot.'/local/ulcc_form_library/plugin/form_elements';

// get all the currently installed form element plugins
$form_element_plugins = form_records_to_menu($dbc->get_form_element_plugins(), 'id', 'name');

//this section gets language strings for all plugins
foreach ($form_element_plugins as $plugin_file) {

    if (file_exists($plugins.'/'.$plugin_file.".php"))  {


        require_once($plugins.'/'.$plugin_file.".php");
        // instantiate the object
        $class = basename($plugin_file, ".php");
        $resourceobj = new $class();
        $method = array($resourceobj, 'language_strings');


        //check whether the language string element has been defined
        if (is_callable($method,true)) {
            $resourceobj->language_strings($string);
        }
    }
}


















