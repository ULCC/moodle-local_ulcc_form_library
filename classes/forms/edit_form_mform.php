<?php 

/**
 * This class makes the form that is used to create forms
 *
 * @copyright &copy; 2011 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package form library
 * @version 1.0
 */

global $CFG;

require_once("$CFG->libdir/formslib.php");


class edit_form_mform extends moodleform {

        public		$form_id;
        public      $pluginname;
        public      $plugintype;
		public		$dbc;
        public      $templates;

	
		/**
     	 * TODO comment this
     	 */
		function __construct($pluginname,$plugintype,$form_id=null) {

			global $CFG;

			$this->form_id	    =	$form_id;
            $this->pluginname   =   $pluginname;
            $this->plugintype   =   $plugintype;

			$this->dbc			=	new form_db();

            // Lets check if the plugin has been created in the form library plugin table.
            $moodleplugin       =   $this->dbc->get_moodle_plugin($pluginname, $plugintype);

            $this->plugin_id    =   (!empty($moodleplugin)) ?   $moodleplugin->id   : null;





            // call the parent constructor
            parent::__construct("{$CFG->wwwroot}/local/ulcc_form_library/actions/edit_form.php?form_id={$this->form_id}");
		}
		
		/**
     	 * TODO comment this
     	 */		
		function definition() {
			 global $USER, $CFG;

        	$dbc = new form_db;

        	$mform =& $this->_form;
        	
        	$fieldsettitle = (!empty($this->form_id)) ? get_string('editform', 'local_ulcc_form_library') : get_string('createform', 'local_ulcc_form_library');
        	
        	//create a new fieldset
        	$mform->addElement('html', '<fieldset id="reportfieldset" class="clearfix ilpfieldset">');
           $mform->addElement('html', '<legend >'.$fieldsettitle.'</legend>');
        	
        	$mform->addElement('hidden', 'id');
        	$mform->setType('id', PARAM_INT);

            $mform->addElement('hidden', 'creator_id', $USER->id);
            $mform->setType('creator_id', PARAM_INT);

            $mform->addElement('hidden', 'moodlepluginname', $this->pluginname);
            $mform->setType('moodlepluginname', PARAM_TEXT);

            $mform->addElement('hidden', 'moodleplugintype', $this->plugintype);
            $mform->setType('moodleplugintype', PARAM_TEXT);

            $mform->addElement('hidden', 'plugin_id', $this->plugin_id);
            $mform->setType('plugin_id', PARAM_INT);

        	// NAME element
            $mform->addElement(
                'text',
                'name',
                get_string('name', 'local_ulcc_form_library'),
                array('class' => 'form_input')
            );
            $mform->addRule('name', null, 'maxlength', 255, 'client');
	        $mform->addRule('name', null, 'required', null, 'client');
	        $mform->setType('name', PARAM_RAW);

            // DESCRIPTION element
            $mform->addElement(
                'htmleditor',
                'description',
                get_string('description', 'local_ulcc_form_library'),
                array('class' => 'form_input', 'rows'=> '10', 'cols'=>'65')
            );

            $mform->addRule('description', null, 'maxlength', 65535, 'client');

            $mform->setType('description', PARAM_RAW);

            //TODO add the elements to implement the frequency functionlaity
            if (stripos($CFG->release,"2.") !== false) {
                $mform->addElement('filepicker', 'binary_icon',get_string('binary_icon', 'local_ulcc_form_library'), null, array('maxbytes' => FORM_MAXFILE_SIZE, 'accepted_types' => FORM_ICON_TYPES));
            } else {
                $this->set_upload_manager(new upload_manager('binary_icon', false, false, 0, false, FORM_MAXFILE_SIZE, true, true, false));
                $mform->addElement('file', 'binary_icon', get_string('binary_icon', 'local_ulcc_form_library'));
            }

            $mform->addElement(
                'text',
                'identifier',
                get_string('identifier', 'local_ulcc_form_library'),
                array('class' => 'form_input')
            );

            $buttonarray[] = $mform->createElement('submit', 'saveanddisplaybutton', get_string('submit'));
	        $buttonarray[] = &$mform->createElement('cancel');
	        
	        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
	        
	        //close the fieldset
	        $mform->addElement('html', '</fieldset>');
		}



        function validation( $data ){
            $data   =   (object)    $data;

            $this->errors = array();
        }

		
		/**
     	 * TODO comment this
     	 */		
		function process_data($data) {
			global $CFG;
			
			if (empty($data->id)) {

            	$data->id = $this->dbc->create_form($data);

        	} else {
			
				//check to stop report icons from being overwritten
				//if the binary_icon param is empty unset it that will stop 
				//any data that is currently present from being overwritten
				if (empty($data->binary_icon)) unset($data->binary_icon); 

            	$this->dbc->update_form($data);
        	}
	
    	    return $data->id;
		}
		
		/**
     	 * TODO comment this
     	 */
    	function definition_after_data() {
    		
    	}
	
}

	
?>
