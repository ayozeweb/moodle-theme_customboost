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
     * @param string $source Which courses: recent, inprogress, starred or all.
     * @param string[] $elements Optional parts of the card.
     */
    public function __construct(
        /** @var \stdClass The user. */
        protected \stdClass $user,
        /** @var int Maximum number of cards. */
        protected int $limit = 6,
        /** @var string Which courses: recent, inprogress, starred or all. */
        protected string $source = 'recent',
        /** @var string[] Optional parts of the card: category, progress, enddate, continue. */
        protected array $elements = ['category', 'progress'],
    ) {
    }

    /**
     * The courses to show, according to the "Courses shown" setting, up to the limit.
     *
     * - recent: most recently accessed first, then the rest of the enrolled courses that have not finished;
     * - inprogress: courses Moodle classifies as in progress (started, not finished, not completed);
     * - starred: the courses the user starred;
     * - all: every enrolled course.
     * All but "recent" are ordered by last access, then by name.
     *
     * @return \stdClass[]
     */
    public function get_courses(): array {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/course/lib.php');

        $fields = ['summary', 'summaryformat', 'startdate', 'enddate', 'enablecompletion'];
        if ($this->source === 'recent') {
            $courses = [];
            foreach (course_get_recent_courses($this->user->id, $this->limit) as $course) {
                $courses[$course->id] = $course;
            }
            if (count($courses) < $this->limit) {
                foreach (enrol_get_my_courses($fields, 'fullname ASC', $this->limit * 2) as $course) {
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

        $courses = enrol_get_my_courses($fields, 'fullname ASC');
        if ($this->source === 'inprogress') {
            $courses = array_filter($courses, fn($course) =>
                course_classify_for_timeline($course, $this->user) === COURSE_TIMELINE_INPROGRESS);
        } else if ($this->source === 'starred') {
            $service = \core_favourites\service_factory::get_service_for_user_context(\context_user::instance($this->user->id));
            $starred = array_map(
                fn($favourite) => (int) $favourite->itemid,
                $service->find_favourites_by_type('core_course', 'courses')
            );
            $courses = array_filter($courses, fn($course) => in_array((int) $course->id, $starred, true));
        }

        $lastaccess = $DB->get_records_menu('user_lastaccess', ['userid' => $this->user->id], '', 'courseid, timeaccess');
        uasort($courses, fn($a, $b) => [($lastaccess[$b->id] ?? 0), $a->fullname] <=> [($lastaccess[$a->id] ?? 0), $b->fullname]);
        return array_slice(array_values($courses), 0, $this->limit);
    }

    /**
     * Export for the template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        global $CFG;
        // Every string is exported as plain text (tags stripped, not entity-escaped) and the template escapes it once
        // with {{ }}. Escaping here as well would show "&amp;" or "&#039;" in names such as "R&D" or "O'Brien".
        $plain = ['escape' => false];
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
            $show = fn(string $element) => in_array($element, $this->elements, true);
            $cards[] = [
                'id' => $course->id,
                'fullname' => format_string($course->fullname, true, ['context' => $context] + $plain),
                'viewurl' => (new \core\url('/course/view.php', ['id' => $course->id]))->out(false),
                'image' => $image,
                'category' => $show('category') && $category ? $category->get_formatted_name($plain) : '',
                'hasprogress' => $show('progress') && $hasprogress,
                'progress' => $percentage,
                'progresstext' => get_string('progresspercent', 'theme_customboost', $percentage),
                'completed' => $hasprogress && $percentage >= 100,
                'enddate' => $show('enddate') && !empty($course->enddate)
                    ? userdate($course->enddate, get_string('strftimedatefullshort', 'langconfig'))
                    : '',
                'continue' => $show('continue'),
                'notstarted' => $hasprogress && $percentage === 0,
                'hidden' => empty($course->visible),
            ];
        }

        return [
            'greeting' => $this->text('dashboardgreeting', 'greeting', $plain),
            'subtitle' => $this->text('dashboardsubtitle', 'greeting_subtitle', $plain),
            'sectiontitle' => get_string('dashboardsection_' . $this->source, 'theme_customboost'),
            'courses' => $cards,
            'hascourses' => !empty($cards),
            // My courses is optional in 5.3 (off by default); without it the list is the overview block on this page.
            'mycoursesurl' => empty($CFG->enablemycourses) ? null : (new \core\url('/my/courses.php'))->out(false),
        ];
    }

    /**
     * The greeting or the subtitle: the administrator's text (multi-language filters applied) or the default string,
     * with {firstname} replaced.
     *
     * @param string $setting dashboardgreeting or dashboardsubtitle.
     * @param string $default Language string used when the setting is empty.
     * @param array $plain format_string options.
     * @return string Plain text.
     */
    protected function text(string $setting, string $default, array $plain): string {
        $firstname = format_string($this->user->firstname, true, $plain);
        $custom = trim((string) get_config('theme_customboost', $setting));
        if ($custom === '') {
            return get_string($default, 'theme_customboost', $firstname);
        }
        $text = format_string($custom, true, ['context' => \context_system::instance()] + $plain);
        return str_replace('{firstname}', $firstname, $text);
    }
}
