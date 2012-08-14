<?php
/**
 * Provides a interface that can be used to access form data and display forms
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package Form Library
 * @version 1.0
 */


//require the form db class
require_once($CFG->dirroot.'/local/ulcc_form_library/db/form_db.class.php');

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/forms/form_entry_mform.php');

require_once($CFG->dirroot.'/local/ulcc_form_library/classes/form_parser.class.php');

class ulcc_form {

    private     $plugintype;

    private     $pluginname;

    private     $dbc;

    private     $formdata;

    function __construct($plugintype,$pluginname)   {
        global $CFG;

        $this->plugintype   =   $plugintype;
        $this->pluginname   =   $pluginname;
        $this->dbc          =   new form_db();
        $this->formdata     =   null;

    }

    /**
     * Returns an array contain all forms that have been created for the current plugin
     *
     * @param  string $formtype filter on form type
     *
     * @return array of objects or false
     *
     */
    function get_plugin_forms($formtype=null,$disabled=false) {
        return $this->dbc->get_plugin_forms($this->pluginname,$this->plugintype,$formtype,$disabled);
    }



    function display_form($form_id,$pageurl,$cancelurl,$entry_id=null) {
        global  $PARSER, $SESSION;

        //check if the form is part of the current plugin

        if ($this->dbc->is_plugin_form($this->pluginname,$this->plugintype,$form_id))   {

            $f      =   $this->dbc->get_form_by_id($form_id);

            if (!empty($f->status) && empty($f->deleted)) {

                //check if the form is multipaged
                $is_multipaged  =   $this->dbc->element_type_exists($form_id,'ulcc_form_plg_pb');

                //get the current page variable if it exists
                $currentpage    =   optional_param('current_page',1,PARAM_INT);

                //unset the current page variable otherwise moodleform will take it and use it in the
                //in the current form (which will overwrite any changes we make to the current page element)
                unset($_POST['current_page']);

                $page_data        =   optional_param('page_data',0,PARAM_RAW);

                //The page_data element is part of all forms if it is not found and there is a session var for this form
                //then it must be for all data unset it
                if (empty($page_data) && isset($SESSION->pagedata[$form_id])) unset($SESSION->pagedata[$form_id]);

                if (!empty($is_multipaged)) {
                    $nextpressed        =   optional_param('nextbutton',0,PARAM_RAW);
                    $previouspressed    =   optional_param('previousbutton',0,PARAM_RAW);
                }

                //if the next button has been pressed increment the page number by 1
                if (!empty($nextpressed))   {
                    $currentpage++;
                }

                //if the previous button has been pressed decrease the page number by 1
                if (!empty($previouspressed))   {
                    $currentpage--;
                }

                $mform   =   new form_entry_mform($form_id, $this->plugintype, $this->pluginname, $pageurl, $entry_id, $currentpage);

                //set the current page variable inside of the form


                //check if the form has already been submitted if not display the form.
                if ($mform->is_cancelled()) {
                    //send the user back to dashboard
                    redirect($cancelurl, '', FORM_REDIRECT_DELAY);
                }

                //was the form submitted?
                // has the form been submitted?
                if($mform->is_submitted()) {

                    $mform->next($form_id,$currentpage);

                    $mform->previous($form_id,$currentpage);

                    $temp   =   new stdClass();
                    $temp->currentpage  =   $currentpage;
                    $mform->set_data($temp);

                        //get the form data submitted
                        $formdata = $mform->get_multipage_data($form_id);;

                        $this->formdata =   $formdata;

                        if (isset($formdata->submitbutton))   {

                             //contains process_data
                             $success =  $mform->submit($form_id);

                            //we no longer need the form information for this page
                            unset($SESSION->pagedata[$form_id]);

                            //if saving the data was not successful
                            if(!$success) {
                                //print an error message
                                print_error(get_string("entrycreationerror", 'block_ilp'), 'block_ilp');
                            }

                            return $success;
                       }
                }

                //loads the data into the form
                $mform->load_entry($entry_id);

                $mform->display();

            }
        }
    }

    /**
     * returns the data for the specified entry
     *
     * @param $entry_id
     */
    function get_form_entry($entry_id)    {

        $entrydata		=	false;

        //get the main entry record
        $entry	=	$this->dbc->get_form_entry($entry_id);

        if (!empty($entry)) 	{
            $mform      =   new form_entry_mform($entry->form_id, false, false, false);
            $entrydata  =   $mform->return_entry($entry_id);
        }

        return (!empty($entrydata)) ? $entrydata  : false  ;
    }

    /**
     *
     */
    function display_form_entry($entry_id,$removeelement = array())   {
        global  $CFG;

        $entrydata		=	false;

        //get the main entry record
        $entry	=	$this->dbc->get_form_entry($entry_id);

        $formentry    =   get_string('entrynotfound','local_ulcc_form_library');

        if (!empty($entry)) 	{
            $mform      =   new form_entry_mform($entry->form_id, false, false, false);
            $entrydata  =   $mform->return_entry($entry_id,true,$removeelement);

            if (!empty($entrydata)) {
                ob_start();
                include_once($CFG->dirroot."/local/ulcc_form_library/views/entry_display.html");

                $formentry = ob_get_contents();

                ob_end_clean();
            }


        }

        return $formentry;
    }

    /**
     *
     *
     * @param $fieldname
     * @return mixed
     */
    function get_form_field_value($fieldname) {
        if (!empty($this->formdata) && isset($this->formdata->$fieldname))  {
            return  $this->formdata->$fieldname;
        }
    }

    /**
     * Returns the value of the form element specified
     *
     * @param int    $entry_id      the id of the entry whose value will be returned
     * @param string $elementtype   the name of the element that will be returned
     * @param bool   $rawvalue      should the raw value be returned or should the value be passed through
     *                              the form elements view function
     */
    function get_form_element_value($entry_id,$elementtype,$rawvalue)   {
        global $CFG;

        $entry      =   $this->dbc->get_form_entry($entry_id);
        $formelement    =   $this->dbc->get_form_element_by_name($elementtype);

        if (!empty($entry) && !empty($formelement))   {
            if ($formfields     =   $this->dbc->element_occurances($entry->form_id,$formelement->tablename)) {

                $formdata   =   new stdClass();

                //take the name field from the plugin as it will be used to call the instantiate the plugin class
                $classname = $formelement->name;

                //instantiate the form element class
                $formelementclass	=	new $classname();

                // include the class for the plugin
                include_once("{$CFG->dirroot}/local/ulcc_form_library/plugin/form_elements/{$classname}.php");

                foreach ($formfields as $ff)    {

                    $formelementclass->load($ff->id);

                    //call the plugin class entry data method
                    if (empty($rawvalue))   {
                        $formelementclass->view_data($ff->id,$entry_id,$formdata);
                    } else {
                        $formelementclass->entry_data($ff->id,$entry_id,$formdata);
                    }
                }

                $fielddata  =   array();

                foreach ($formdata  as $field)  {
                    $fielddata[]    =   $field;
                }

                return $fielddata;

            }
        }

        return false;
    }

    /**
     * Returns true or false based on whether the form with the id given has a element of the type specified
     *
     * @param int       $form_id   the id of the form that we will check for the element
     * @param string    $elementtype the element type that will be looked for.
     */
    function has_element_type($form_id,$elementtype)  {
        $formelement    =   $this->dbc->get_form_element_by_name($elementtype);
        $formfields     =   $this->dbc->element_occurances($form_id,$formelement->tablename);
        return  (!empty($formfields))  ? true  : false;
    }


    function create_form_entry($form_id,$creator_id)    {
        global $CFG;

        $entry					=	new stdClass();
        $entry->form_id		    =	$form_id;
        $entry->creator_id		=	$creator_id;

        $entry_id	=	$this->dbc->create_entry($entry);

        //get all of the fields in the current report, they will be returned in order as
        //no position has been specified
        $formfields		=	$this->dbc->get_form_fields_by_position($form_id);

        $data   =   new stdClass();

        foreach ($formfields as $field) {

            //get the plugin record that for the plugin
            $formelementrecord	=	$this->dbc->get_form_element_plugin($field->formelement_id);

            //take the name field from the plugin as it will be used to call the instantiate the plugin class
            $classname = $formelementrecord->name;

            // include the class for the plugin
            include_once("{$CFG->dirroot}/local/ulcc_form_library/plugin/form_elements/{$classname}.php");

            if(!class_exists($classname)) {
                print_error('noclassforplugin', 'block_ilp', '', $formelementrecord->name);
            }

            //instantiate the plugin class
            $pluginclass	=	new $classname();

            $pluginclass->load($field->id);


            $formfield      =       $field->id.'_fieldname';

            if (get_parent_class($pluginclass) != 'form_element_plugin_itemlist')   {
                $data->$formfield   =   "";
            }   else    {
                //get items for this instance of the form element
                $items  =   $this->dbc->get_optionlist($field->id , $formelementrecord->tablename, $field );
                if (!empty($items)) {
                    $item   =   array_pop($items);
                    $data->$formfield   =  $item->id;
                }
            }


            //call the plugins entry_form function which will add an instance of the plugin
            //to the form
            if ($pluginclass->is_processable())	{
                if (!$pluginclass->entry_process_data($field->id,$entry_id,$data)) $result = false;
            }
        }

    }

    /**
     * Sets the value of a particular form element within the given entry
     *
     * @param $entry_id     int     the id of the entry whose value will be set
     * @param $elementtype  string  the name of the element that will be set
     * @param $value        mixed   the value to be set
     * @param int $occurance int    the occurance to set e.g 1 = first 2 = 2nd etc
     */
    function set_form_element_entry_value($entry_id,$elementtype,$value,$occurance=1)   {

        global  $CFG;

        $entry      =   $this->dbc->get_form_entry($entry_id);
        $formelement    =   $this->dbc->get_form_element_by_name($elementtype);

        if (!empty($entry) && !empty($formelement))   {
            if ($formfields     =   $this->dbc->element_occurances($entry->form_id,$formelement->tablename)) {

                $formdata   =   new stdClass();

                //take the name field from the plugin as it will be used to call the instantiate the plugin class
                $classname = $formelement->name;

                //instantiate the form element class
                $formelementclass	=	new $classname();

                // include the class for the plugin
                include_once("{$CFG->dirroot}/local/ulcc_form_library/plugin/form_elements/{$classname}.php");

                $i          =   0;
                $data       =   new stdClass();

                if (!empty($formfields))   {
                    foreach ($formfields as $ff)    {

                        $formelementclass->load($ff->id);

                        $formfield      =       $ff->id.'_fieldname';

                        $data->$formfield   =   $value;

                        //call the plugins entry_form function which will add an instance of the plugin
                        //to the form
                        if ($formelementclass->is_processable() && $i = $occurance-1)	{
                            if (!$formelementclass->entry_process_data($ff->id,$entry_id,$data)) $result = false;
                        }

                        $i++;
                    }

                    return true;
                }

            }
        }

        return false;



    }

}
