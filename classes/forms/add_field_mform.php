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

global $CFG;

require_once("$CFG->libdir/formslib.php");


class add_field_mform extends moodleform {

    public		$form_id;
    public      $pluginname;
    public      $plugintype;
    public		$dbc;

    /**
     * TODO comment this
     */
    function __construct($pluginname,$plugintype,$context_id,$form_id) {
        global  $CFG;

        $this->form_id      =   $form_id;
        $this->pluginname   =   $pluginname;
        $this->plugintype   =   $plugintype;
        $this->context_id   =   $context_id;

        // call the parent constructor
        parent::__construct("{$CFG->wwwroot}/local/ulcc_form_library/actions/edit_formfields.php?form_id={$this->form_id}&moodlepluginname={$this->pluginname}&moodleplugintype={$this->plugintype}&context_id={$context_id}");

    }


    /**
     * TODO comment this
     */
    function definition() {

        global $USER, $CFG;

        require_once($CFG->dirroot.'/local/ulcc_form_library/lib.php');

        $dbc = new form_db;

        $mform =& $this->_form;

        //get all of the installed form element plugins
        $formelementplugins		=	$dbc->get_form_element_plugins();

        $frmplugins				=	array(''=>get_string('addfield','local_ulcc_form_library'));

        //if no elements installed pass an empty array
        if (empty($formelementplugins)) {
            $formelementplugins = array();
        }

        $elements   =   get_plugin_config($this->plugintype,$this->pluginname);



        //append _description to the name field so there description can be picked up from lang file
        foreach ($formelementplugins as $plg) {
            if (empty($elements))   {
                $frmplugins[$plg->id] = get_string($plg->name.'_description','local_ulcc_form_library');
            } else {
                if (in_array($plg->name,$elements))   {
                    $frmplugins[$plg->id] = get_string($plg->name.'_description','local_ulcc_form_library');
                }
            }
        }

        $fieldsettitle = get_string('addfield', 'local_ulcc_form_library');

        //create a new fieldset
        $mform->addElement('html', '<fieldset id="reportfieldset" class="clearfix ilpfieldset">');
        $mform->addElement('html', '<legend class="ftoggler">'.$fieldsettitle.'</legend>');

        $mform->addElement('hidden', 'form_id',$this->form_id);
        $mform->setType('form_id', PARAM_INT);

        $mform->addElement('hidden', 'moodlepluginname', $this->pluginname);
        $mform->setType('moodlepluginname', PARAM_TEXT);

        $mform->addElement('hidden', 'moodleplugintype', $this->plugintype);
        $mform->setType('moodleplugintype', PARAM_TEXT);

        $mform->addElement('hidden', 'context_id', $this->context_id);
        $mform->setType('context_id', PARAM_INT);

        $mform->addElement('select', 'formelement_id', get_string('addfield', 'local_ulcc_form_library'), $frmplugins);
        $mform->addRule('formelement_id', null, 'required', null, 'client');
        $mform->setType('formelement_id', PARAM_INT);

        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('addfield','local_ulcc_form_library'));

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);

        //close the fieldset
        $mform->addElement('html', '</fieldset>');
    }

    /**
     * TODO comment this
     */
    function process_data($data) {
        return $data->plugin_id;
    }



}