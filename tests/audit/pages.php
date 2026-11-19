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
 * Print, as JSON, the pages that tests/audit/audit.mjs walks through, resolved against the demo data.
 *
 * Run tests/fixtures/demo_site.php first. Test sites only.
 *
 *   php theme/customboost/tests/audit/pages.php > pages.json
 *
 * @package    theme_customboost
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../../config.php');

$course = $DB->get_record('course', ['shortname' => 'DEMO'], '*', MUST_EXIST);
$cms = [];
foreach (get_fast_modinfo($course)->get_cms() as $cm) {
    $cms[$cm->modname] = $cm->id;
}
$discussion = $DB->get_field('forum_discussions', 'id', ['course' => $course->id], IGNORE_MULTIPLE);
$c = $course->id;
$coursecontext = context_course::instance($c)->id;
// The activity chooser opens from the "+" menu of a section, in editing mode.
$openchooser = 'edit,click:[data-action="open-addingcontent"],click:.dropdown-menu.show [data-action="open-chooser"]';

// Each page: an id used in file names, the URL, who views it (anon, ana = student, prof = teacher, admin) and optional
// actions run before the checks, comma separated: "edit" turns editing on, "click:<selector>" clicks a button.
$pages = [
    ['login', '/login/index.php', 'anon'],
    ['forgotpassword', '/login/forgot_password.php', 'anon'],
    ['signup', '/login/signup.php', 'anon'],
    ['sitehome', '/index.php?redirect=0', 'ana'],
    ['dashboard', '/my/', 'ana'],
    ['mycourses', '/my/courses.php', 'ana'],
    ['categories', '/course/index.php', 'ana'],
    ['search', '/course/search.php?search=web', 'ana'],
    ['course', "/course/view.php?id={$c}", 'ana'],
    ['course-edit', "/course/view.php?id={$c}", 'prof', 'edit'],
    ['activity-chooser', "/course/view.php?id={$c}", 'prof', $openchooser],
    ['contentbank', "/contentbank/index.php?contextid={$coursecontext}", 'prof'],
    ['book', "/mod/book/view.php?id={$cms['book']}", 'ana'],
    ['assign', "/mod/assign/view.php?id={$cms['assign']}", 'ana'],
    ['assign-grading', "/mod/assign/view.php?id={$cms['assign']}&action=grading", 'prof'],
    ['forum', "/mod/forum/discuss.php?d={$discussion}", 'ana'],
    ['choice', "/mod/choice/view.php?id={$cms['choice']}", 'ana'],
    ['quiz', "/mod/quiz/view.php?id={$cms['quiz']}", 'ana'],
    ['quiz-attempt', "/mod/quiz/view.php?id={$cms['quiz']}", 'ana', 'click:form[action*="startattempt.php"] [type=submit]'],
    ['grades-user', "/grade/report/user/index.php?id={$c}", 'ana'],
    ['grades-grader', "/grade/report/grader/index.php?id={$c}", 'prof'],
    ['participants', "/user/index.php?id={$c}", 'prof'],
    ['course-settings', "/course/edit.php?id={$c}", 'prof'],
    ['calendar', '/calendar/view.php?view=month', 'ana'],
    ['messages', '/message/index.php', 'ana'],
    ['notifications', '/message/output/popup/notifications.php', 'ana'],
    ['profile', '/user/profile.php', 'ana'],
    ['preferences', '/user/preferences.php', 'ana'],
    ['privatefiles', '/user/files.php', 'ana'],
    ['badges', '/badges/mybadges.php', 'ana'],
    ['error', '/course/view.php?id=999999', 'ana'],
    ['admin-search', '/admin/search.php', 'admin'],
    ['admin-users', '/admin/user.php', 'admin'],
    ['admin-theme', '/admin/settings.php?section=themesettingcustomboost', 'admin'],
    ['admin-theme-tools', '/theme/customboost/tools.php', 'admin'],
];

// Self registration only exists when an authentication plugin offers it.
if (empty($CFG->registerauth)) {
    $pages = array_values(array_filter($pages, fn($p) => $p[0] !== 'signup'));
}

echo json_encode(array_map(fn($p) => [
    'id' => $p[0],
    'url' => $p[1],
    'user' => $p[2],
    'action' => $p[3] ?? null,
], $pages), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
