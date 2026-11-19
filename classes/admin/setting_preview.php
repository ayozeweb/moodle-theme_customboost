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

namespace theme_customboost\admin;

/**
 * A settings page element that shows a live preview of the appearance settings. It stores nothing.
 *
 * @package    theme_customboost
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class setting_preview extends \admin_setting {
    /**
     * Constructor.
     *
     * @param string $name 'plugin/name'.
     * @param string $visiblename
     * @param string $description
     */
    public function __construct($name, $visiblename, $description) {
        $this->nosave = true;
        parent::__construct($name, $visiblename, $description, '');
    }

    /**
     * Nothing is stored.
     *
     * @return bool
     */
    public function get_setting() {
        return true;
    }

    /**
     * Nothing is stored.
     *
     * @return bool
     */
    public function get_defaultsetting() {
        return true;
    }

    /**
     * Nothing is stored.
     *
     * @param mixed $data
     * @return string Empty: no error.
     */
    public function write_setting($data) {
        return '';
    }

    /**
     * The two preview panels, filled in by the theme_customboost/preview module.
     *
     * @param mixed $data
     * @param string $query
     * @return string
     */
    public function output_html($data, $query = '') {
        global $OUTPUT, $PAGE, $SITE;
        $PAGE->requires->js_call_amd('theme_customboost/preview', 'init', ['[data-region="ct-preview"]']);
        $html = $OUTPUT->render_from_template('theme_customboost/admin_preview', [
            'sitename' => format_string($SITE->shortname, true, ['context' => \context_system::instance(), 'escape' => false]),
            'modes' => [
                ['mode' => 'light', 'label' => get_string('preview_light', 'theme_customboost')],
                ['mode' => 'dark', 'label' => get_string('preview_dark', 'theme_customboost')],
            ],
        ]);
        return format_admin_setting($this, $this->visiblename, $html, $this->description, false, '', null, $query);
    }
}
