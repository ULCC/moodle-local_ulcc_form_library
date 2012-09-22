<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package
 * @version
 */

/**
 * This is a library class that provides reworked versions of some functions
 * from /lib/moodlelib.php, which are used to parse data received via POST
 * and GET.
 *
 * @copyright &copy; 2011 University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package ILP
 * @version 2.0
 */

// Fetch the table library.
global $CFG;
require_once($CFG->dirroot.'/local/ulcc_form_library/constants.php');

/**
 * Manages the url parameters.
 */
class form_parser {

    /**
     * @var array
     */
    protected $params;

    /**
     * Returns all the params parsed for a given page.
     *
     */
    public function get_params() {
        return $this->params;
    }

    /**
     * Returns all params parsed for given page as a query string
     *
     * @param array $included defines the url params that will be included in
     * the returned string. If nothing is defined then all params are returned
     *
     * @return string the query string
     */
    public function get_params_url($included = array()) {
        $querystr = '';
        foreach ($this->params as $p => $v) {
            // Don't send empty stuff except 0.
            if ($v !== '' && !is_null($v) && (empty($included) || in_array($p, $included))) {
                if (!empty($querystr)) {
                    $querystr .= '&';
                }
                $querystr .= $p.'='.$v;
            }
        }
        return $querystr;
    }

    /**
     * Returns a particular value for the named variable, taken from
     * POST or GET.  If the parameter doesn't exist then an error is
     * thrown because we require this variable.
     *
     * This function should be used to initialise all required values
     * in a script that are based on parameters.  Usually it will be
     * used like this:
     *    $id = required_param('id');
     *
     * This function is being modified so that it will now fail if
     * a empty string is passed
     *
     * @uses required_param()
     * @param string $parname the name of the page parameter we want
     * @param int|string $type expected type of parameter
     * @return mixed
     */
    public function required_param($parname, $type = PARAM_CLEAN) {

        // Detect_unchecked_vars addition.
        global $CFG;
        if (!empty($CFG->detect_unchecked_vars)) {
            global $UNCHECKED_VARS;
            unset ($UNCHECKED_VARS->vars[$parname]);
        }

        if (isset($_POST[$parname])) { // POST has precedence.
            $param = $_POST[$parname];
        } else if (isset($_GET[$parname])) {
            $param = $_GET[$parname];
        } else {
            print_error('missingparam', '', '', $parname);
        }

        if ($parname == "") {
            print_error('emptyparam', '', '', $parname);
        }

        $retparam = $this->clean_param($param, $type);

        if ($retparam === false) {
            print_error('wrongparam', 'local_ulcc_form_library', '', $parname);
        } else {
            // Add the param to the list.
            $this->params[$parname] = $retparam;

            return $retparam;
        }
    }

    /**
     * Returns a particular value for the named variable, taken from
     * POST or GET, otherwise returning a given default. Also stores it so that all of them can be
     * retrieved easily later using get_params_url().
     *
     * This function should be used to initialise all optional values
     * in a script that are based on parameters.  Usually it will be
     * used like this:
     *    $name = optional_param('name', 'Fred');
     *
     * This function has been modifed so that any param that contains the empty
     * string will now pass the default value instead.
     *
     * @uses optional_param()
     * @param string $parname the name of the page parameter we want
     * @param mixed  $default the default value to return if nothing is found
     * @param int|string $type expected type of parameter
     * @return mixed
     */
    public function optional_param($parname, $default = NULL, $type = PARAM_CLEAN) {

        // Detect_unchecked_vars addition.
        global $CFG;
        if (!empty($CFG->detect_unchecked_vars)) {
            global $UNCHECKED_VARS;
            unset ($UNCHECKED_VARS->vars[$parname]);
        }

        if (isset($_POST[$parname])) { // POST has precedence.
            $param = $_POST[$parname];
        } else if (isset($_GET[$parname])) {
            $param = $_GET[$parname];
        } else {
            // Add the param to the list.
            $this->params[$parname] = $default;

            return $default;
        }

        if ($param == "") {
            // Add the param to the list.
            $this->params[$parname] = $default;

            return $default;
        }

        $retparam = $this->clean_param($param, $type);

        if ($retparam === false) {
            print_error('wrongparamopt', '', '', $parname);
        } else {
            // Add the param to the list.
            $this->params[$parname] = $retparam;

            return $retparam;
        }
    }

    /**
     * This is a wrapper function used to give clean_param the ability to distinguish
     * if a int has been passed when PARAM_INT is declared as the variable type.
     * If a int has not been passed false is returned. For all other types normal
     * operation of the clean_param function takes place.
     *
     * @uses clean_param()
     * @param mixed $param the variable we are cleaning
     * @param int $type expected format of param after cleaning.
     * @return mixed
     */
    protected function clean_param($param, $type) {
        if ($type == PARAM_INT) {
            if (preg_match('/[0-9]+/', $param) == 0) {
                return false;
            }
        }

        if ($type == FORM_PARAM_ARRAY) {
            if (!is_array($param)) {
                return false;
            } else {
                // TODO need to code some tests on the array.
                return $param;
            }
        }

        return clean_param($param, $type);
    }
}

// Create a global instance of the parser class.
global $PARSER;
$PARSER = new form_parser();
