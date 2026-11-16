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
}
