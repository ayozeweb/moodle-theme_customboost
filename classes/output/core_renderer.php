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

/**
 * Core renderer for theme_customboost.
 *
 * @package    theme_customboost
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer extends \theme_boost\output\core_renderer {
    /**
     * Page header, followed on the personal area by the greeting and the course cards.
     *
     * @return string
     */
    public function full_header() {
        $header = parent::full_header();
        if (
            $this->page->pagelayout !== 'mydashboard' || !isloggedin() || isguestuser()
                || !get_config('theme_customboost', 'dashboardcourses')
        ) {
            return $header;
        }
        global $USER;
        $limit = (int) (get_config('theme_customboost', 'dashboardcoursecount') ?: 6);
        $dashboard = new dashboard($USER, $limit);
        return $header . $this->render_from_template(
            'theme_customboost/dashboard',
            $dashboard->export_for_template($this)
        );
    }
}
