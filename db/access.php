<?php

/**
 * Capability definitions for the ulcc form library.
 *
 * The capabilities are loaded into the database table when the module is
 * installed or updated. Whenever the capability definitions are updated,
 * the module version number should be bumped up.
 *
 * @copyright &copy; 2012 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package form
 * @version 1.0
 */

//      TOFO
$capabilities    =   array(

    //form admin definition

    'local/ulcc_form_library:formadmin'   =>  array(
        'captype'       =>  'write',
        'contextlevel'  =>  CONTEXT_SYSTEM,
        'legacy'        =>  array(
             'manager'    =>  CAP_ALLOW
        )
    ),
);
