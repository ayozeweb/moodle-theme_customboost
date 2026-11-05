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

namespace theme_customboost\output;

use core\output\renderable;
use core\output\renderer_base;
use core\output\templatable;
use core_course\external\course_summary_exporter;
use core_course_category;
use core_completion\progress;

/**
 * Personal area (dashboard) header: greeting and cards of the courses accessed most recently.
 *
 * @package    theme_customboost
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dashboard implements renderable, templatable {
    /**
     * Constructor.
     *
     * @param \stdClass $user The user the dashboard belongs to.
     * @param int $limit Maximum number of course cards.
     */
    public function __construct(
        /** @var \stdClass The user. */
        protected \stdClass $user,
        /** @var int Maximum number of cards. */
        protected int $limit = 6,
    ) {
    }

    /**
     * The courses to show: most recently accessed first, then the rest of the enrolled courses.
     *
     * @return \stdClass[]
     */
    public function get_courses(): array {
        global $CFG;
        require_once($CFG->dirroot . '/course/lib.php');

        $courses = [];
        foreach (course_get_recent_courses($this->user->id, $this->limit) as $course) {
            $courses[$course->id] = $course;
        }
        if (count($courses) < $this->limit) {
            $enrolled = enrol_get_my_courses(
                ['summary', 'summaryformat', 'startdate', 'enddate'],
                'fullname ASC',
                $this->limit * 2
            );
            foreach ($enrolled as $course) {
                if (count($courses) >= $this->limit) {
                    break;
                }
                // Finished courses do not belong in "continue learning".
                if (!isset($courses[$course->id]) && (empty($course->enddate) || $course->enddate > time())) {
                    $courses[$course->id] = $course;
                }
            }
        }
        return array_values($courses);
    }

    /**
     * Export for the template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        global $CFG;
        $cards = [];
        foreach ($this->get_courses() as $course) {
            $context = \context_course::instance($course->id);
            $percentage = progress::get_course_progress_percentage($course, $this->user->id);
            $hasprogress = $percentage !== null;
            $percentage = $hasprogress ? (int) floor($percentage) : 0;
            $image = course_summary_exporter::get_course_image($course);
            if (!$image) {
                $image = $output->get_generated_image_for_id($course->id);
            }
            $category = core_course_category::get($course->category, IGNORE_MISSING, true);
            $fullname = format_string($course->fullname, true, ['context' => $context]);
            $cards[] = [
                'id' => $course->id,
                'fullname' => $fullname,
                'continuelabel' => get_string('continuecoursename', 'theme_customboost', $fullname),
                'viewurl' => (new \core\url('/course/view.php', ['id' => $course->id]))->out(false),
                'image' => $image,
                'category' => $category ? $category->get_formatted_name() : '',
                'hasprogress' => $hasprogress,
                'progress' => $percentage,
                'progresstext' => get_string('progresspercent', 'theme_customboost', $percentage),
                'completed' => $hasprogress && $percentage >= 100,
                'notstarted' => $hasprogress && $percentage === 0,
                'hidden' => empty($course->visible),
            ];
        }

        return [
            'greeting' => get_string('greeting', 'theme_customboost', format_string($this->user->firstname)),
            'courses' => $cards,
            'hascourses' => !empty($cards),
            // My courses is optional in 5.3 (off by default); without it the list is the overview block on this page.
            'mycoursesurl' => empty($CFG->enablemycourses) ? null : (new \core\url('/my/courses.php'))->out(false),
        ];
    }
}
