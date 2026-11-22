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

use theme_customboost\hook_listener;
use theme_customboost\options;

/**
 * Core renderer for theme_customboost.
 *
 * @package    theme_customboost
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer extends \theme_boost\output\core_renderer {
    /** @var bool Whether main_content() already printed the skip link target before the personal area greeting. */
    protected $ctskiptargetprinted = false;

    /**
     * The main region. On the personal area it starts with the greeting and the course cards: they are the main
     * content of that page, so they belong in the main landmark and the "Skip to main content" link lands on them.
     *
     * @return string
     */
    public function main_content() {
        $hero = $this->dashboard_hero();
        if ($hero === '') {
            return parent::main_content();
        }
        // Core prints the skip link target right before the page content, which would put it after the cards. Print
        // it here, before them, and skip_link_target() leaves core's one out.
        $this->ctskiptargetprinted = true;
        return '<div role="main">' . parent::skip_link_target('maincontent') . $hero . $this->unique_main_content_token .
            '</div>';
    }

    /**
     * Skip link target; the main content one is left out when main_content() printed it.
     *
     * @param string $id
     * @return string
     */
    public function skip_link_target($id = null) {
        if ($id === 'maincontent' && $this->ctskiptargetprinted) {
            return '';
        }
        return parent::skip_link_target($id);
    }

    /**
     * The personal area greeting and course cards, or an empty string on any other page.
     *
     * @return string
     */
    protected function dashboard_hero(): string {
        // Only the dashboard gets the cards. Both checks are needed: messaging reuses the mydashboard layout and
        // My courses reuses the my-index page type.
        if (
            $this->page->pagelayout !== 'mydashboard' || $this->page->pagetype !== 'my-index'
                || !isloggedin() || isguestuser()
                || !get_config('theme_customboost', 'dashboardcourses')
        ) {
            return '';
        }
        global $USER;
        $limit = (int) (get_config('theme_customboost', 'dashboardcoursecount') ?: 6);
        $elements = options::get('dashboardcardelements');
        $dashboard = new dashboard(
            $USER,
            $limit,
            options::get('dashboardsource'),
            $elements === '' ? [] : explode(',', $elements)
        );
        return $this->render_from_template('theme_customboost/dashboard', $dashboard->export_for_template($this));
    }

    /**
     * The full logo: the dark version when the page is known to render dark.
     *
     * The mode is known on the server for the dark preset and for a user who chose dark in Boost's colour modes. With
     * the automatic preset it is only known in the browser, so the light logo is used (documented in the guide).
     *
     * @param int|null $maxwidth
     * @param int $maxheight
     * @return \core\url|false
     */
    public function get_logo_url($maxwidth = null, $maxheight = 200) {
        if (hook_listener::current_mode() === hook_listener::PRESET_DARK && ($dark = $this->dark_logo_url('logodark'))) {
            return $dark;
        }
        return parent::get_logo_url($maxwidth, $maxheight);
    }

    /**
     * URL of the compact logo for dark mode, or false. The header prints both and CSS shows the right one, so it also
     * works with the automatic preset.
     *
     * @return \core\url|false
     */
    public function dark_compact_logo_url() {
        return $this->should_display_navbar_logo() ? $this->dark_logo_url('logocompactdark') : false;
    }

    /**
     * Whether the header shows the site name next to the compact logo (without a logo it is always shown).
     *
     * @return bool
     */
    public function show_navbar_sitename(): bool {
        return (bool) get_config('theme_customboost', 'showsitename');
    }

    /**
     * The administrator's footer text, filtered for the system context.
     *
     * @return string
     */
    public function ct_footer_text(): string {
        $text = trim((string) get_config('theme_customboost', 'footertext'));
        if ($text === '') {
            return '';
        }
        return format_text($text, FORMAT_HTML, ['context' => \context_system::instance()]);
    }

    /**
     * URL of one of the dark logo settings, or false when it is not set.
     *
     * Returned as a URL object like core's get_logo_url(): callers such as the login form call out() on it.
     *
     * @param string $setting logodark or logocompactdark.
     * @return \core\url|false
     */
    protected function dark_logo_url(string $setting) {
        if ($this->page->theme->name !== 'customboost') {
            // A child theme of this one has its own settings.
            return false;
        }
        $filepath = (string) get_config('theme_customboost', $setting);
        if ($filepath === '') {
            return false;
        }
        return \core\url::make_pluginfile_url(
            \context_system::instance()->id,
            'theme_customboost',
            $setting,
            theme_get_revision(),
            dirname($filepath) === '/' ? '/' : dirname($filepath) . '/',
            basename($filepath)
        );
    }
}
