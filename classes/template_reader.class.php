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
 * Reads in a template file from the given moodle plugin if present and returns all valid temnplates
 *
 * @copyright &copy; @{YEAR} University of London Computer Centre
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package form library
 * @version 1.0
 */

class template_reader {

    public $file;
    public $pluginname;
    public $plugintype;
    private $templates;


    function __construct($type, $name) {

        global $CFG;

        $this->pluginname = $name;
        $this->plugintype = $type;

        $typedir = ($type == 'block') ? 'blocks' : $type;

        $this->file = $CFG->dirroot."/{$typedir}/{$name}/template.flt";

        $this->templates = false;

        $this->readfile();

        var_dump($this->templates);
    }

    /**
     * Reads a xml file of templates in and converts it to a object
     */
    private function readfile() {
        if (file_exists($this->file)) {
            $temp = simplexml_load_file($this->file);

            // Check the given templates make sure they have all of the correct nodes and values
            // are within the given range then move on.
            $this->templates = $this->validate_templates($temp);
        }
    }

    /**
     * Returns whether the current moodle plugin has a template file
     *
     * @return bool
     */
    public function is_templates() {
        return (!empty($this->templates)) ? true : false;
    }

    /**
     *
     * @param array $template
     * @return array
     */
    private function validate_templates($template) {

        $tmp = array();

        if (isset($template->template)) {
            foreach ($template->template as $t) {
                if (!isset($t->name)) continue;
                if (!isset($t->element)) continue;

                foreach ($t->element as $e) {
                    if (!isset($e->label)) continue 2;
                    if (!isset($e->plugin)) continue 2;
                }

                $tmp[] = $t;
            }
        }

        return $tmp;
    }

    public function allowed() {

    }


}