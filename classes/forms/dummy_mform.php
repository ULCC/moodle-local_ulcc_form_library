<?php

/**
 * Dummy mform I am using to make sure the html_editor and filepicker JS is loaded on pages where the ulcc_form class is
 * called
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package
 * @version
 */


global $CFG;

require_once("$CFG->libdir/formslib.php");


class dummy_mform extends moodleform {



    /**
     * TODO comment this
     */
    function __construct() {

        // call the parent constructor
        parent::__construct();
    }

    /**
     * TODO comment this
     */
    function definition() {
        global $USER, $CFG;

        $dbc = new form_db;

        $mform =& $this->_form;

        // DESCRIPTION element
        $mform->addElement(
            'editor',
            'description',
            get_string('description', 'local_ulcc_form_library'),
            array('class' => 'form_input', 'rows'=> '10', 'cols'=>'65')
        );
        $mform->addElement('filepicker', 'binary_icon',get_string('binary_icon', 'local_ulcc_form_library'), null, array('maxbytes' => FORM_MAXFILE_SIZE, 'accepted_types' => FORM_ICON_TYPES));

    }



    function validation( $data ){
    }


    /**
     * TODO comment this
     */
    function process_data($data) {
    }

    /**
     * TODO comment this
     */
    function definition_after_data() {

    }

}


?>
