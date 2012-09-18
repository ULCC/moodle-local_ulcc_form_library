<?php

/**
 * A collection of functions used within the form library code.
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package form library
 * @version 1.0
 */


/**
 * Utility function which makes a recordset into an array
 * Similar to recordset_to_menu. Array is keyed by the specified field of each record and
 * either has the second specified field as the value, or the results of the callback function which
 * takes the second field as it's first argument
 *
 * field1, field2 is needed because the order from get_records_sql is not reliable
 * @param records - records from get_records_sql() or get_records()
 * @param field1 - field to be used as menu index
 * @param field2 - feild to be used as coresponding menu value
 * @param string $callback (optional) the name of a function to call in order ot generate the menu item for each record
 * @param string $callbackparams (optional) the extra parameters for the callback function
 * @return mixed an associative array, or false if an error occured or the RecordSet was empty.
 */
function form_records_to_menu($records, $field1, $field2, $callback = null, $callbackparams = null) {

    $menu = array();

    if(!empty($records)) {
        foreach ($records as $record) {
            if(empty($callback)) {
                $menu[$record->$field1] = $record->$field2;
            } else {
                // array_unshift($callbackparams, $record->$field2);
                $menu[$record->$field1] = call_user_func_array($callback,array($record->$field2,$callbackparams));
            }
        }

    }
    return $menu;
}

/**
 * returns
 *
 * @param $context
 * @param $filearea
 * @param $args
 * @param $forcedownload
 */
function ulcc_form_library_pluginfile($context, $filearea, $args, $forcedownload) {

    if ($context->contextlevel != CONTEXT_SYSTEM) {
        send_file_not_found();
    }

    require_login();

    if ($filearea !== 'form_element_plugin_file') {
        send_file_not_found();
    }

    $fs = get_file_storage();

    $filename = array_pop($args);
    $itemid   = array_pop($args);
    $filepath = $args ? '/'.implode('/', $args).'/' : '/';

    if (!$file = $fs->get_file($context->id, 'ulcc_form_library', $filearea, $itemid, $filepath, $filename) or $file->is_directory()) {
        send_file_not_found();
    }

    session_get_instance()->write_close();
    send_stored_file($file, 60*60, 0, $forcedownload);
}

/**
 * Returns plugin config options for the module with the details provided
 *
 * @param $type
 * @param $name
 */
function get_plugin_config($type,$name)    {
    global  $CFG;

    $path   =   '';

    switch ($type)   {
        case    'mod':
            $path   =  $CFG->dirroot.'/mod/'.$name.'/config_uflib.xml';
            break;

        case    'block':
            $path   .=  $CFG->dirroot.'/blocks/'.$name.'/config_uflib.xml';
            break;
    }



    if (!empty($path))  {
        //get the xml file if it exists
        if (file_exists($path)) {
            $xmlfile    =   file_get_contents($path);
            $configopt  =   simplexml_load_string($xmlfile);
            $elements   =   array();
            foreach ($configopt->element as $e) {
                   $elements[]   =     (string) $e;
            }
            return (isset($elements))    ? $elements  : false  ;
        }
    }
}

/**
 * @param object $data holding form's submitted data
 *
 */
function check_array($data)    {

    foreach ($data as $key=>$item) {
        if (is_array($item) && array_key_exists('text', $item)) {
            $data->$key = $item['text'];
        }
    }

return $data;
}




