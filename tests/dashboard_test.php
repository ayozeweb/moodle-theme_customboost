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

namespace theme_customboost;

use theme_customboost\output\dashboard;

/**
 * Tests for the personal area course cards.
 *
 * @package    theme_customboost
 * @category   test
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\theme_customboost\output\dashboard::class)]
final class dashboard_test extends \advanced_testcase {
    /**
     * Cards list enrolled courses with progress, most recently accessed first, up to the limit.
     */
    public function test_export_for_template(): void {
        global $CFG, $DB, $PAGE;
        require_once($CFG->libdir . '/completionlib.php');
        $this->resetAfterTest();
        $CFG->enablecompletion = 1;

        $gen = $this->getDataGenerator();
        $user = $gen->create_user(['firstname' => 'Ana']);
        $courses = [];
        foreach ([1, 2, 3] as $i) {
            $courses[$i] = $gen->create_course(['fullname' => "Course $i", 'enablecompletion' => 1]);
            $gen->enrol_user($user->id, $courses[$i]->id, 'student');
        }
        $page = $gen->create_module('page', ['course' => $courses[2]->id, 'completion' => COMPLETION_TRACKING_MANUAL]);
        $gen->create_module('page', ['course' => $courses[2]->id, 'completion' => COMPLETION_TRACKING_MANUAL]);
        $cm = get_coursemodule_from_instance('page', $page->id);
        (new \completion_info($courses[2]))->update_state($cm, COMPLETION_COMPLETE, $user->id);
        $DB->insert_record('user_lastaccess', ['userid' => $user->id, 'courseid' => $courses[2]->id, 'timeaccess' => time()]);

        $this->setUser($user);
        $data = (new dashboard($user, 2))->export_for_template($PAGE->get_renderer('core'));

        $this->assertTrue($data['hascourses']);
        $this->assertCount(2, $data['courses']);
        $this->assertSame('Course 2', $data['courses'][0]['fullname']);
        $this->assertTrue($data['courses'][0]['hasprogress']);
        $this->assertSame(50, $data['courses'][0]['progress']);
        $this->assertStringContainsString('Ana', $data['greeting']);
        $this->assertNotEmpty($data['courses'][0]['image']);
    }

    /**
     * No enrolments: empty state.
     */
    public function test_no_courses(): void {
        global $PAGE;
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $data = (new dashboard($user))->export_for_template($PAGE->get_renderer('core'));
        $this->assertFalse($data['hascourses']);
        $this->assertSame([], $data['courses']);
    }

    /**
     * Names with HTML special characters are escaped exactly once and markup never reaches the page.
     */
    public function test_strings_are_escaped_once(): void {
        global $PAGE;
        $this->resetAfterTest();

        $gen = $this->getDataGenerator();
        $user = $gen->create_user(['firstname' => "O'Brien"]);
        $category = $gen->create_category(['name' => 'R&D <b>lab</b>']);
        $course = $gen->create_course([
            'fullname' => 'Maths & <script>alert(1)</script>Stats',
            'category' => $category->id,
        ]);
        $gen->enrol_user($user->id, $course->id, 'student');
        $this->setUser($user);

        $renderer = $PAGE->get_renderer('core');
        $data = (new dashboard($user))->export_for_template($renderer);

        // Exported as plain text: tags stripped, no entities.
        $this->assertSame("Hello, O'Brien", $data['greeting']);
        $this->assertSame('R&D lab', $data['courses'][0]['category']);
        $this->assertSame('Maths & alert(1)Stats', $data['courses'][0]['fullname']);

        // Rendered: escaped once by the template.
        $html = $renderer->render_from_template('theme_customboost/dashboard', $data);
        $this->assertStringContainsString('R&amp;D lab', $html);
        $this->assertStringContainsString('Maths &amp; alert(1)Stats', $html);
        $this->assertMatchesRegularExpression('/Hello, O(&#39;|&#039;|&apos;)Brien/', $html);
        $this->assertStringNotContainsString('<script>alert', $html);
        $this->assertStringNotContainsString('<b>lab', $html);
        $this->assertStringNotContainsString('&amp;amp;', $html);
        $this->assertStringNotContainsString('&amp;#', $html);
    }

    /**
     * Thirty courses: the limit is respected, recent ones come first and finished ones only appear if visited.
     */
    public function test_many_courses_limit_and_order(): void {
        global $DB, $PAGE;
        $this->resetAfterTest();
        $gen = $this->getDataGenerator();
        $user = $gen->create_user();
        $courses = [];
        for ($i = 1; $i <= 30; $i++) {
            $courses[$i] = $gen->create_course(['fullname' => sprintf('Course %02d', $i)]);
            $gen->enrol_user($user->id, $courses[$i]->id, 'student');
        }
        // Finished course, never visited: must not fill an empty slot.
        $finished = $gen->create_course(['fullname' => 'Course 00 finished', 'startdate' => time() - 2 * YEARSECS,
            'enddate' => time() - YEARSECS]);
        $gen->enrol_user($user->id, $finished->id, 'student');
        // Two recent visits, the newest first.
        $DB->insert_record('user_lastaccess', ['userid' => $user->id, 'courseid' => $courses[17]->id, 'timeaccess' => time() - 60]);
        $DB->insert_record('user_lastaccess', ['userid' => $user->id, 'courseid' => $courses[23]->id, 'timeaccess' => time()]);
        $this->setUser($user);

        $data = (new dashboard($user, 12))->export_for_template($PAGE->get_renderer('core'));
        $names = array_column($data['courses'], 'fullname');

        $this->assertCount(12, $names);
        $this->assertSame(['Course 23', 'Course 17'], array_slice($names, 0, 2));
        $this->assertNotContains('Course 00 finished', $names);
        $this->assertSame(count($names), count(array_unique($names)));
    }

    /**
     * Card states: no completion tracking, completed, hidden course.
     */
    public function test_card_states(): void {
        global $CFG, $PAGE;
        require_once($CFG->libdir . '/completionlib.php');
        $this->resetAfterTest();
        $CFG->enablecompletion = 1;
        $gen = $this->getDataGenerator();
        $user = $gen->create_user();
        $plain = $gen->create_course(['fullname' => 'No tracking', 'enablecompletion' => 0]);
        $done = $gen->create_course(['fullname' => 'Done', 'enablecompletion' => 1]);
        $hidden = $gen->create_course(['fullname' => 'Hidden', 'visible' => 0]);
        // Completion is only tracked for students; the hidden course is only listed for its teachers.
        $gen->enrol_user($user->id, $plain->id, 'student');
        $gen->enrol_user($user->id, $done->id, 'student');
        $gen->enrol_user($user->id, $hidden->id, 'editingteacher');
        $page = $gen->create_module('page', ['course' => $done->id, 'completion' => COMPLETION_TRACKING_MANUAL]);
        (new \completion_info($done))->update_state(
            get_coursemodule_from_instance('page', $page->id),
            COMPLETION_COMPLETE,
            $user->id
        );
        $this->setUser($user);

        $cards = [];
        foreach ((new dashboard($user))->export_for_template($PAGE->get_renderer('core'))['courses'] as $card) {
            $cards[$card['fullname']] = $card;
        }

        $this->assertFalse($cards['No tracking']['hasprogress']);
        $this->assertTrue($cards['Done']['completed']);
        $this->assertSame(100, $cards['Done']['progress']);
        $this->assertTrue($cards['Hidden']['hidden']);
        // Without a course image every card still gets a generated pattern.
        $this->assertStringStartsWith('data:image/svg+xml', $cards['No tracking']['image']);
    }

    /**
     * The four sources: recent, in progress, starred and all.
     */
    public function test_sources(): void {
        global $CFG, $DB, $PAGE;
        require_once($CFG->libdir . '/completionlib.php');
        $this->resetAfterTest();
        $CFG->enablecompletion = 1;
        $gen = $this->getDataGenerator();
        $user = $gen->create_user();
        $past = $gen->create_course(['fullname' => 'Past', 'startdate' => time() - 2 * YEARSECS, 'enddate' => time() - YEARSECS]);
        $current = $gen->create_course(['fullname' => 'Current', 'startdate' => time() - WEEKSECS,
            'enddate' => time() + YEARSECS]);
        $future = $gen->create_course(['fullname' => 'Future', 'startdate' => time() + YEARSECS]);
        foreach ([$past, $current, $future] as $course) {
            $gen->enrol_user($user->id, $course->id, 'student');
        }
        $DB->insert_record('user_lastaccess', ['userid' => $user->id, 'courseid' => $past->id, 'timeaccess' => time()]);
        $this->setUser($user);
        \core_favourites\service_factory::get_service_for_user_context(\context_user::instance($user->id))
            ->create_favourite('core_course', 'courses', $future->id, \context_course::instance($future->id));

        $names = function (string $source) use ($user, $PAGE): array {
            $data = (new dashboard($user, 6, $source))->export_for_template($PAGE->get_renderer('core'));
            return array_column($data['courses'], 'fullname');
        };
        $this->assertSame(['Past', 'Current', 'Future'], $names('recent'));
        $this->assertSame(['Current'], $names('inprogress'));
        $this->assertSame(['Future'], $names('starred'));
        // All: last accessed first, then by name.
        $this->assertSame(['Past', 'Current', 'Future'], $names('all'));
    }

    /**
     * Card elements and the greeting texts.
     */
    public function test_elements_and_texts(): void {
        global $PAGE;
        $this->resetAfterTest();
        $gen = $this->getDataGenerator();
        $user = $gen->create_user(['firstname' => 'Ana']);
        $course = $gen->create_course(['enddate' => make_timestamp(2030, 6, 30)]);
        $gen->enrol_user($user->id, $course->id, 'student');
        $this->setUser($user);
        $renderer = $PAGE->get_renderer('core');

        $card = (new dashboard($user, 6, 'all', []))->export_for_template($renderer)['courses'][0];
        $this->assertSame('', $card['category']);
        $this->assertSame('', $card['enddate']);
        $this->assertFalse($card['continue']);

        $card = (new dashboard($user, 6, 'all', ['category', 'enddate', 'continue']))->export_for_template($renderer)['courses'][0];
        $this->assertNotSame('', $card['category']);
        $this->assertStringContainsString('30', $card['enddate']);
        $this->assertTrue($card['continue']);

        set_config('dashboardgreeting', 'Welcome back, {firstname}! <b>bold</b>', 'theme_customboost');
        set_config('dashboardsubtitle', '{firstname}, your courses', 'theme_customboost');
        $data = (new dashboard($user))->export_for_template($renderer);
        $this->assertSame('Welcome back, Ana! bold', $data['greeting']);
        $this->assertSame('Ana, your courses', $data['subtitle']);
        $this->assertSame(get_string('dashboardsection_recent', 'theme_customboost'), $data['sectiontitle']);
    }
}
