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
 * Created by JetBrains PhpStorm.
 * User: Nigel.Daley
 * Date: 15/06/12
 * Time: 18:05
 * To change this template use File | Settings | File Templates.
 */

define('FORM_LIB_ACTIONS_PATH', '/local/ulcc_form_library/actions');

define('FORM_LIB_VIEWS_PATH', '/local/ulcc_form_library/views');

define('FORM_LIB_CLASSES_PATH', '/local/ulcc_form_library/classes');

define('FORM_LIB_DB_PATH', '/local/ulcc_form_library/db');

// Constants used by the logging class.
define('FORM_LOG_ADD', 1);

define('FORM_LOG_UPDATE', 2);

define('FORM_LOG_DELETE', 3);

// The mamximum size of uploaded files.
define('FORM_MAXFILE_SIZE', 1048576);

// The type of files that may be uploaded as icons.
define('FORM_ICON_TYPES', 'jpg,png, jpeg, gif');


define('FORM_REDIRECT_DELAY', 1);

// Used when changing the position of a field in a report.
define('FORM_MOVE_UP', '1');
define('FORM_MOVE_DOWN', '0');

// Used by the date and date_deadline plugin to define what type of date may be.
// Accepted in a report.
define('FORM_PASTDATE', 1);
define('FORM_PRESENTDATE', 2);
define('FORM_FUTUREDATE', 3);
define('FORM_ANYDATE', 0);

define('FORM_OPTIONSINGLE', 1);
define('FORM_OPTIONMULTI', 2);

// Defines whether something is enabled or disabled.
define('FORM_ENABLED', 1);
define('FORM_DISABLED', 0);

define('FORM_STATE_UNSET', 0);
define('FORM_STATE_FAIL', 1);
define('FORM_STATE_PASS', 2);
define('FORM_STATE_NOTCOUNTED', 3);

define('FORM_PARAM_ARRAY', 0x40000);

define('FORM_STRIP_TAGS_DESCRIPTION', '');
