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

use theme_customboost\output\core_renderer;

/**
 * Tests for the theme renderer: dark logos, site name, footer text.
 *
 * @package    theme_customboost
 * @category   test
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(core_renderer::class)]
final class renderer_test extends \advanced_testcase {
    /**
     * The renderer of a page using the theme.
     *
     * @return core_renderer
     */
    private function renderer(): core_renderer {
        $page = new \moodle_page();
        $page->set_context(\context_system::instance());
        $page->set_url('/');
        $page->force_theme('customboost');
        $renderer = $page->get_renderer('core', null, RENDERER_TARGET_GENERAL);
        $this->assertInstanceOf(core_renderer::class, $renderer);
        return $renderer;
    }

    /**
     * Store a logo file for a setting.
     *
     * @param string $component
     * @param string $area
     */
    private function add_logo(string $component, string $area): void {
        get_file_storage()->create_file_from_string(['contextid' => \context_system::instance()->id,
            'component' => $component, 'filearea' => $area, 'itemid' => 0, 'filepath' => '/',
            'filename' => $area . '.png'], 'png');
        set_config($area, '/' . $area . '.png', $component);
    }

    /**
     * The dark logos are URL objects (the login form calls out() on them) and are only used when the page is dark.
     */
    public function test_dark_logos(): void {
        $this->resetAfterTest();
        $this->add_logo('core_admin', 'logo');
        $this->add_logo('core_admin', 'logocompact');

        // Without dark logos: core's.
        set_config('colourpreset', 'dark', 'theme_customboost');
        $this->assertStringContainsString('core_admin/logo', $this->renderer()->get_logo_url()->out());
        $this->assertFalse($this->renderer()->dark_compact_logo_url());

        $this->add_logo('theme_customboost', 'logodark');
        $this->add_logo('theme_customboost', 'logocompactdark');
        $logo = $this->renderer()->get_logo_url();
        $this->assertInstanceOf(\core\url::class, $logo);
        $this->assertStringContainsString('theme_customboost/logodark/', $logo->out());
        $this->assertStringContainsString('logocompactdark.png', $this->renderer()->dark_compact_logo_url()->out());

        // Light and automatic presets: the normal logo on the login page.
        foreach (['light', 'auto'] as $preset) {
            set_config('colourpreset', $preset, 'theme_customboost');
            $this->assertStringContainsString('core_admin/logo', $this->renderer()->get_logo_url()->out(), $preset);
        }
    }

    /**
     * Site name toggle and footer text (filtered, empty when unset).
     */
    public function test_sitename_and_footer(): void {
        $this->resetAfterTest();
        $this->assertFalse($this->renderer()->show_navbar_sitename());
        set_config('showsitename', 1, 'theme_customboost');
        $this->assertTrue($this->renderer()->show_navbar_sitename());

        $this->assertSame('', $this->renderer()->ct_footer_text());
        set_config('footertext', '<p>Legal <script>alert(1)</script>notice</p>', 'theme_customboost');
        $footer = $this->renderer()->ct_footer_text();
        $this->assertStringContainsString('Legal', $footer);
        $this->assertStringNotContainsString('<script>', $footer);
    }
}
