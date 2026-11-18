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
 * Create the demonstration data used for screenshots and for the audit in tests/audit.
 *
 * FOR TEST SITES ONLY: it creates users with well known passwords. It refuses to run unless --confirm is given.
 * It is safe to run twice: existing users and courses are reused.
 *
 *   php theme/customboost/tests/fixtures/demo_site.php --confirm
 *
 * @package    theme_customboost
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->libdir . '/testing/generator/lib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/calendar/lib.php');

[$options] = cli_get_params(['confirm' => false, 'help' => false], ['h' => 'help']);
if ($options['help'] || !$options['confirm']) {
    cli_writeln('Creates demo users (ana / prof, password "Demo1234!") and courses. Test sites only.');
    cli_writeln('php theme/customboost/tests/fixtures/demo_site.php --confirm');
    exit($options['help'] ? 0 : 1);
}

\core\cron::setup_user();
set_config('enablecompletion', 1);
set_config('enablemycourses', 1);
// Self registration and the guest button, so that the audit covers every login-flow screen.
$auths = get_enabled_auth_plugins();
if (!in_array('email', $auths)) {
    $auths[] = 'email';
    set_config('auth', implode(',', $auths));
}
set_config('registerauth', 'email');
set_config('guestloginbutton', 1);
$gen = new testing_data_generator();

// Users.
$users = [];
foreach (
    [
        ['ana', 'Ana', 'García', 'ana@example.com'],
        ['prof', 'Marta', 'Suárez', 'prof@example.com'],
    ] as [$username, $firstname, $lastname, $email]
) {
    $user = $DB->get_record('user', ['username' => $username, 'deleted' => 0]);
    if (!$user) {
        $user = $gen->create_user(['username' => $username, 'firstname' => $firstname, 'lastname' => $lastname,
            'email' => $email]);
    }
    update_internal_user_password($user, 'Demo1234!');
    $users[$username] = $user;
}

// Categories.
$categories = [];
foreach (['Digital marketing', 'Languages', 'Technology', 'Soft skills'] as $name) {
    $category = $DB->get_record('course_categories', ['name' => $name], '*', IGNORE_MULTIPLE);
    $categories[] = $category ?: $gen->create_category(['name' => $name]);
}

// Courses: full name, category index, number of lessons, lessons completed by the student.
$definitions = [
    'C1' => ['Social media strategy for small businesses', 0, 4, 3],
    'C2' => ['English B2: professional communication', 1, 6, 2],
    'C3' => ['Web development with HTML and CSS', 2, 5, 5],
    'C4' => ['Data analysis with spreadsheets', 2, 6, 1],
    'C5' => ['Customer service excellence', 3, 4, 0],
    'C6' => ['Project management fundamentals', 3, 5, 4],
];
$fs = get_file_storage();
$index = 0;
foreach ($definitions as $shortname => [$fullname, $categoryindex, $lessons, $done]) {
    $index++;
    if ($DB->record_exists('course', ['shortname' => $shortname])) {
        continue;
    }
    $course = $gen->create_course(['fullname' => $fullname, 'shortname' => $shortname,
        'category' => $categories[$categoryindex]->id, 'enablecompletion' => 1, 'numsections' => 4,
        'summary' => 'Demonstration course.']);
    $fs->create_file_from_pathname(
        ['contextid' => context_course::instance($course->id)->id, 'component' => 'course',
        'filearea' => 'overviewfiles', 'itemid' => 0, 'filepath' => '/', 'filename' => 'cover.jpg'],
        __DIR__ . "/images/course{$index}.jpg"
    );
    $gen->enrol_user($users['ana']->id, $course->id, 'student');
    $gen->enrol_user($users['prof']->id, $course->id, 'editingteacher');
    $completion = new completion_info($course);
    for ($lesson = 0; $lesson < $lessons; $lesson++) {
        $page = $gen->create_module('page', ['course' => $course->id, 'name' => 'Lesson ' . ($lesson + 1),
            'section' => ($lesson % 4) + 1, 'completion' => COMPLETION_TRACKING_MANUAL,
            'content' => '<h3>Introduction</h3><p>Lorem ipsum dolor sit amet, <a href="#">consectetur</a> adipiscing elit.</p>'
                . '<ul><li>First point</li><li>Second point</li></ul><pre>body { margin: 0; }</pre>']);
        if ($lesson < $done) {
            $completion->update_state(
                get_coursemodule_from_instance('page', $page->id),
                COMPLETION_COMPLETE,
                $users['ana']->id
            );
        }
    }
    $DB->insert_record('user_lastaccess', ['userid' => $users['ana']->id, 'courseid' => $course->id,
        'timeaccess' => time() - $index * HOURSECS]);
}

// One course with the most common activity types, for the page walk-through.
if (!$DB->record_exists('course', ['shortname' => 'DEMO'])) {
    $course = $gen->create_course(['fullname' => 'Demo: every kind of activity', 'shortname' => 'DEMO',
        'category' => $categories[2]->id, 'enablecompletion' => 1, 'numsections' => 3]);
    $gen->enrol_user($users['ana']->id, $course->id, 'student');
    $gen->enrol_user($users['prof']->id, $course->id, 'editingteacher');
    $fs->create_file_from_pathname(
        ['contextid' => context_course::instance($course->id)->id, 'component' => 'course',
            'filearea' => 'overviewfiles', 'itemid' => 0, 'filepath' => '/', 'filename' => 'cover.jpg'],
        __DIR__ . '/images/course2.jpg'
    );

    $gen->create_module('label', ['course' => $course->id, 'section' => 1,
        'intro' => '<p><strong>Welcome.</strong> This section shows text, links and a table.</p>'
            . '<table class="table"><thead><tr><th>Week</th><th>Topic</th><th>Hours</th></tr></thead>'
            . '<tbody><tr><td>1</td><td>Basics</td><td>4</td></tr><tr><td>2</td><td>Practice</td><td>6</td></tr></tbody></table>']);
    $gen->create_module('url', ['course' => $course->id, 'section' => 1, 'name' => 'Reference site',
        'externalurl' => 'https://example.com']);
    $book = $gen->create_module('book', ['course' => $course->id, 'section' => 1, 'name' => 'Handbook']);
    $bookgen = $gen->get_plugin_generator('mod_book');
    foreach (['Getting started', 'Core concepts', 'Next steps'] as $title) {
        $bookgen->create_chapter(['bookid' => $book->id, 'title' => $title,
            'content' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor.</p>']);
    }

    $assign = $gen->create_module('assign', ['course' => $course->id, 'section' => 2, 'name' => 'Project brief',
        'intro' => 'Write a one page brief for your project.', 'assignsubmission_onlinetext_enabled' => 1,
        'duedate' => time() + 5 * DAYSECS, 'completion' => COMPLETION_TRACKING_MANUAL]);
    $forum = $gen->create_module('forum', ['course' => $course->id, 'section' => 2, 'name' => 'Questions and answers']);
    $forumgen = $gen->get_plugin_generator('mod_forum');
    $discussion = $forumgen->create_discussion(['course' => $course->id, 'forum' => $forum->id,
        'userid' => $users['prof']->id, 'name' => 'Welcome to the course', 'message' => 'Introduce yourself here.']);
    $forumgen->create_post(['discussion' => $discussion->id, 'parent' => $discussion->firstpost,
        'userid' => $users['ana']->id, 'message' => 'Hello, I am Ana and I work in marketing.']);
    $gen->create_module('choice', ['course' => $course->id, 'section' => 2, 'name' => 'Which day suits you?',
        'option' => ['Monday', 'Wednesday', 'Friday']]);

    $quiz = $gen->create_module('quiz', ['course' => $course->id, 'section' => 3, 'name' => 'Check your knowledge',
        'grade' => 10, 'sumgrades' => 2]);
    $questiongen = $gen->get_plugin_generator('core_question');
    $questioncategory = $questiongen->create_question_category(['contextid' => context_module::instance($quiz->cmid)->id]);
    foreach (['truefalse', 'multichoice'] as $qtype) {
        $question = $questiongen->create_question($qtype, null, ['category' => $questioncategory->id]);
        quiz_add_quiz_question($question->id, $quiz, 0, 1);
    }

    // A grade, so that the gradebook and the user report have something to show.
    $gradeitem = grade_item::fetch(['itemtype' => 'mod', 'itemmodule' => 'assign', 'iteminstance' => $assign->id]);
    if ($gradeitem) {
        $gradeitem->update_final_grade($users['ana']->id, 85, 'demo', 'Clear and well structured.', FORMAT_PLAIN);
    }

    // A calendar event and a message, for the calendar, the timeline and the messaging drawer.
    calendar_event::create((object) ['name' => 'Live session', 'description' => 'Questions and answers.',
        'format' => FORMAT_HTML, 'courseid' => $course->id, 'groupid' => 0, 'userid' => $users['prof']->id,
        'eventtype' => 'course', 'timestart' => time() + 2 * DAYSECS, 'timeduration' => HOURSECS], false);
    $DB->insert_record('user_lastaccess', ['userid' => $users['ana']->id, 'courseid' => $course->id, 'timeaccess' => time()]);
}

$conversation = \core_message\api::get_conversation_between_users([$users['ana']->id, $users['prof']->id]);
if (!$conversation) {
    $conversation = \core_message\api::create_conversation(
        \core_message\api::MESSAGE_CONVERSATION_TYPE_INDIVIDUAL,
        [$users['ana']->id, $users['prof']->id]
    )->id;
    \core_message\api::send_message_to_conversation(
        $users['prof']->id,
        $conversation,
        'Hi Ana, remember the live session this week.',
        FORMAT_PLAIN
    );
}

purge_all_caches();
cli_writeln('Demo data ready. Users: ana, prof (password Demo1234!).');
