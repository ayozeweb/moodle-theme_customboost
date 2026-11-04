<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Login layout: image panel and form, stacked on mobile and side by side from 992 px.
 *
 * @package    theme_customboost
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$bodyattributes = $OUTPUT->body_attributes(['ct-pagelayout-login']);
$systemcontext = context_system::instance();
$sitename = format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), 'escape' => false]);
$sitefullname = format_string($SITE->fullname, true, ['context' => context_course::instance(SITEID), 'escape' => false]);

$loginimage = $PAGE->theme->setting_file_url('loginimage', 'loginimage');
$isdefaultimage = empty($loginimage);
if ($isdefaultimage) {
    $loginimage = $OUTPUT->image_url('login', 'theme_customboost')->out(false);
}

$title = trim((string) get_config('theme_customboost', 'logintitle'));
$tagline = trim((string) get_config('theme_customboost', 'logintagline'));

$leftinstructions = !empty($CFG->auth_instructions)
    ? format_text($CFG->auth_instructions, FORMAT_MOODLE, ['context' => $systemcontext])
    : null;

$templatecontext = [
    'sitename' => $sitename,
    'output' => $OUTPUT,
    'bodyattributes' => $bodyattributes,
    'loginimage' => $loginimage,
    'isdefaultimage' => $isdefaultimage,
    'logintitle' => $title !== '' ? format_string($title, true, ['context' => $systemcontext]) : $sitefullname,
    'logintagline' => $tagline !== '' ? format_string($tagline, true, ['context' => $systemcontext])
        : get_string('logintagline_default', 'theme_customboost'),
    'leftinstructions' => $leftinstructions,
];

echo $OUTPUT->render_from_template('theme_customboost/login', $templatecontext);
