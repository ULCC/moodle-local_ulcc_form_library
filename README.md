moodle-local_ulcc_forms_library
===============================

Forms library

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

Install by unzipping in the local folder so that you have a folder local/ulcc_form_library

This plugin has been written to provide a easy way for developers to include user customisable forms in
their blocks/mods. The plugin handles form creation and data saving without the module (or block) having
to see the work that goes on behind the scenes.

form creation is carried out by passing a user to the local/ulcc_form_library/actions/view_forms.php page. The url must
contain url params 'moodleplugintype' (which should equal either local,block or mod) and 'moodlepluginname'
(which should equal the name of the block, local or mod)

form display is carried out by instantiating the ulcc_form class (local/ulcc_form_library/ulcc_form.class.php)

$uf = new ulcc_form($moodleplugintype,$moodlepluginname);

$currentpageurl    =   $CFG->wwwroot."/block/ilp/actions/entry_form.php";
$cancelurl    =   $CFG->wwwroot."/block/ilp/actions/homepage.php";

$entry_id   =   $uf->display_form($form_id,$currentpageurl,$cancelurl);

to display entry form data use:

$uf->display_form_entry($entry_id);

to obtain all entry data

$uf->get_form_entry($entry_id);

to obtain a list of all forms created for a plugin use

$forms  =   $uf->get_plugin_forms($moodleplugintype,$moodlepluginname);