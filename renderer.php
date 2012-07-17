<?php

/**
 * Renders elements of the ulcc_forms library
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package ulcc_form_library
 * @version 1.0
 */

class local_ulcc_form_library_renderer extends plugin_renderer_base {

    /**
     * A PHP magic method that intercepts all calls to the database class and
     * encodes all the data being input.
     *
     * @param string $method The name of the method being called.
     * @param array $params The array of parameters passed to the method.
     * @return mixed The result of the query.
     */
    function __call($method, $params) {


        // sanatise everything coming into the database here
        $params = $this->encode($params);

        if (method_exists($this,$method))   {
            $classname  =   get_class($this);

            return call_user_func_array(array($classname, $method), $params);
        } else {
            throw new Exception('Undefined class method ' . $method . '() called');
        }
    }




}