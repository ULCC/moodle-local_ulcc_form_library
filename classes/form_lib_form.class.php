<?php

/**
 * Wrapper class for Moodleform, this class adds additional functions to aid inthe creation and usage
 * of multi page forms.
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package forms lib
 * @version 1.0
 *
 *
 */

global  $CFG;

require_once($CFG->libdir.'/formslib.php');

require_once($CFG->dirroot.'/local/ulcc_form_library/db/form_db.class.php');


abstract class form_lib_form extends moodleform  {

    public  $form_id;

    public  $dbc;

    public  $formdata;


    /**
     * Returns data submitted from previous pages on the current form.
     * (this feature is not available using the normal get_data and get_submitted data functions)
     *
     * @param int $form_id the id of the mutlipage form that we want to get submitted data for
     *
     * @return mixed array or null if not data is found
     */
    function get_multipage_data($form_id)   {

        $normdata   =   $this->get_submitted_data();

        $formfields =   $this->dbc->get_form_fields_by_position($form_id);

        if (!empty($formfields))   {

            $elementnames   =   array();
            $data           =   array();

            foreach ($formfields as $ff)    {
                $elementnames[]     =   $ff->id."_field";
            }

            $elementnames[] =   'previousbutton';
            $elementnames[] =   'nextbutton';

            $submiteddata  =   array_merge($_GET,$_POST);

            foreach($submiteddata as $key => $sd)   {
                foreach ($elementnames as $en)  {


                    //we will find anything with a name beginning with the code name of a field
                    //e.g 9_field 9_field_test will both be found and returned
                   if (preg_match("/\b{$en}/i",$key))  {
                           $data[$key]    =   $sd;
                    }
                }
            }




            $normdata   =    (is_array($normdata))  ?   $normdata   :   (array) $normdata;

            return (object) array_merge($normdata,$data);

        }

        return null;
    }




    function next($form_id,$currentpage) {

        global  $SESSION;

        $this->formdata  =  (empty($this->formdata))    ?  $this->get_multipage_data($form_id)   :   $this->formdata ;

        //was the next button pressed
        if (isset($this->formdata->nextbutton))   {

            $cformdata      =   $this->formdata;

            //we do not want any of the following data to be saved as it stop the pagination features from working
            if (isset($cformdata->current_page)) unset($cformdata->current_page);
            if (isset($cformdata->previousbutton))  unset($cformdata->previousbutton);
            if (isset($cformdata->nextbutton))  unset($cformdata->nextbutton);


            //save all data submitted from last page

            //check if the page data array has been created in the session
            if (!isset($SESSION->pagedata)) $SESSION->pagedata  =   array();

            //create a array to hold the page temp_data
            if (!isset($SESSION->pagedata[$form_id]))   $SESSION->pagedata[$form_id] = array();

            if (!isset($SESSION->pagedata[$form_id][$currentpage-1]))   {
                //if no data has been saved for the current page save the data to the dd
                //and save the key
                $SESSION->pagedata[$form_id][$currentpage-1] = $this->dbc->save_temp_data($cformdata);
            } else {
                //if data for this page has already been saved get the key and update the record
                $tempid =   $SESSION->pagedata[$form_id][$currentpage-1];
                $this->dbc->update_temp_data($tempid,$cformdata);
            }

            //set the data in the page to what it equaled before
            if (isset($SESSION->pagedata[$form_id][$currentpage])) {
                $tempdata   =   $this->dbc->get_temp_data($SESSION->pagedata[$form_id][$currentpage]);

                $this->set_data($tempdata);
            }
        }
    }

    /**
     * Carrys out operations necessary if the form is a multipage form and the previous button has been pressed
     */
    function previous($form_id,$currentpage) {
        global $SESSION;

        $this->formdata  =  (empty($this->formdata))    ?  $this->get_multipage_data($form_id)   :   $this->formdata ;

        if (isset($this->formdata->previousbutton)) {

            $cformdata      =   $this->formdata;

            //we do not want any of the following data to be saved as it stop the pagination features from working
            if (isset($cformdata->current_page)) unset($cformdata->current_page);
            if (isset($cformdata->previousbutton))  unset($cformdata->previousbutton);
            if (isset($cformdata->nextbutton))  unset($cformdata->nextbutton);


            if (!isset($SESSION->pagedata[$form_id][$currentpage+1]))   {
                //if no data has been saved for the current page save the data to the dd
                //and save the key
                $SESSION->pagedata[$form_id][$currentpage+1] = $this->dbc->save_temp_data($cformdata);
            } else {
                //if data for this page has already been saved get the key and update the record
                $tempid =   $SESSION->pagedata[$form_id][$currentpage+1];
                $this->dbc->update_temp_data($tempid,$cformdata);
            }

            //set the data in the page to what it equaled before
            if (isset($SESSION->pagedata[$form_id][$currentpage])) {
                $tempdata   =   $this->dbc->get_temp_data($SESSION->pagedata[$form_id][$currentpage]);
                $this->set_data($tempdata);
            }
        }
    }

    function submit($form_id)   {

        global  $SESSION;

        //get all of the submitted data
        $this->formdata  = $this->get_multipage_data($form_id);
        $darray     =   array();

        if (!empty($SESSION->pagedata[$form_id]))   {
            foreach($SESSION->pagedata[$form_id] as $tempid) {
                $tempdata   =   $this->dbc->get_temp_data($tempid);
                $tempdata   =   (is_array($tempdata)) ? $tempdata   :  (array) $tempdata;
                $darray     =   array_merge($darray,$tempdata);
            }
        }

        $formdata   =   (is_array($this->formdata)) ? $this->formdata   :  (array) $this->formdata;

        $formdata =   array_merge($formdata, $darray);

        return $this->process_data($formdata);
    }


    function load_entry($entry_id=false)    {

            global $CFG;

            if (!empty($entry_id))   {

                //create a entry_data object this will hold the data that will be passed to the form
                $entry_data		=	new stdClass();

                //get the main entry record
                $entry	=	$this->dbc->get_form_entry($entry_id);

                if (!empty($entry)) 	{
                    //check if the maximum edit field has been set for this report

                    //get all of the fields in the current report, they will be returned in order as
                    //no position has been specified
                    $formfields		=	$this->dbc->get_form_fields_by_position($entry->form_id);

                    foreach ($formfields as $field) {

                        //get the plugin record that for the plugin
                        $pluginrecord	=	$this->dbc->get_form_element_plugin($field->formelement_id);

                        //take the name field from the plugin as it will be used to call the instantiate the plugin class
                        $classname = $pluginrecord->name;

                        // include the class for the plugin
                        include_once("{$CFG->dirroot}/local/ulcc_form_library/plugin/form_elements/{$classname}.php");

                        if(!class_exists($classname)) {
                            print_error('noclassforplugin', 'local_ulcc_form_library', '', $pluginrecord->name);
                        }

                        //instantiate the plugin class
                        $pluginclass	=	new $classname();

                        $pluginclass->load($field->id);

                        //create the fieldname
                        $fieldname	=	$field->id."_field";


                        $pluginclass->load($field->id);

                        //call the plugin class entry data method
                        $pluginclass->entry_data($field->id,$entry_id,$entry_data);
                    }

                    //set the data in the form
                    $this->set_data($entry_data);
                }
            }
    }


    function return_entry($entry_id=false,$labels=false)    {

        global $CFG;

        if (!empty($entry_id))   {

            //create a entry_data object this will hold the data that will be passed to the form
            $entry_data		=	new stdClass();

            //get the main entry record
            $entry	=	$this->dbc->get_form_entry($entry_id);

            $entrydata  =   array();

            if (!empty($entry)) 	{
                //check if the maximum edit field has been set for this report

                //get all of the fields in the current report, they will be returned in order as
                //no position has been specified
                $formfields		=	$this->dbc->get_form_fields_by_position($entry->form_id);

                foreach ($formfields as $field) {

                    //get the plugin record that for the plugin
                    $pluginrecord	=	$this->dbc->get_form_element_plugin($field->formelement_id);

                    //take the name field from the plugin as it will be used to call the instantiate the plugin class
                    $classname = $pluginrecord->name;

                    // include the class for the plugin
                    include_once("{$CFG->dirroot}/local/ulcc_form_library/plugin/form_elements/{$classname}.php");

                    if(!class_exists($classname)) {
                        print_error('noclassforplugin', 'local_ulcc_form_library', '', $pluginrecord->name);
                    }

                    //instantiate the plugin class
                    $pluginclass	=	new $classname();

                    //create the fieldname
                    $fieldname	=	$field->id."_field";


                    if ($pluginclass->is_viewable() != false)	{
                        $pluginclass->load($field->id);

                        //call the plugin class entry data method
                        $pluginclass->view_data($field->id,$entry->id,$entry_data);

                        if (!empty($labels))   {
                            $fielddata  =   $entry_data->$fieldname;
                            $entry_data->$fieldname     =   array('label'=>$field->label,'value'=>$fielddata);
                        }
                    } else	{
                        $dontdisplay[]	=	$field->id;
                    }
                }

                return $entry_data;
            }
        }

        return false;
    }


}