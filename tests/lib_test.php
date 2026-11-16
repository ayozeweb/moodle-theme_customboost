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

/**
 * Tests for lib.php: SCSS callbacks and file serving.
 *
 * @package    theme_customboost
 * @category   test
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversFunction('theme_customboost_get_pre_scss')]
#[\PHPUnit\Framework\Attributes\CoversFunction('theme_customboost_get_extra_scss')]
#[\PHPUnit\Framework\Attributes\CoversFunction('theme_customboost_get_main_scss_content')]
#[\PHPUnit\Framework\Attributes\CoversFunction('theme_customboost_pluginfile')]
final class lib_test extends \advanced_testcase {
    /**
     * The pre SCSS takes its colours from the token builder.
     */
    public function test_pre_scss_uses_tokens(): void {
        $this->resetAfterTest();
        set_config('brandcolour', '#0f6cbf', 'theme_customboost');
        $scss = theme_customboost_get_pre_scss(\theme_config::load('customboost'));
        $this->assertStringContainsString('$primary: #0f6cbf;', $scss);
        $this->assertStringContainsString('$navbar-height: 56px;', $scss);
    }

    /**
     * A colour setting can never inject CSS or SCSS: anything that is not a hex colour falls back to the default.
     */
    public function test_colour_settings_cannot_inject_css(): void {
        $this->resetAfterTest();
        set_config('brandcolour', 'red;} body {display:none} .x {color: red', 'theme_customboost');
        set_config('accentcolour', '#fff; @import "evil"', 'theme_customboost');
        $theme = \theme_config::load('customboost');

        $output = theme_customboost_get_pre_scss($theme) . theme_customboost_get_extra_scss($theme);
        $this->assertStringNotContainsString('display:none', $output);
        $this->assertStringNotContainsString('evil', $output);
        $this->assertStringContainsString('$primary: ' . tokens::DEFAULT_BRAND . ';', $output);
    }

    /**
     * The whole stylesheet compiles, with the default colours and with a hostile brand colour.
     */
    public function test_scss_compiles(): void {
        $this->resetAfterTest();
        foreach ([null, '#ffcc00'] as $brand) {
            if ($brand) {
                set_config('brandcolour', $brand, 'theme_customboost');
            }
            $theme = \theme_config::load('customboost');
            // The debug variant throws on a compilation error instead of falling back to Boost's CSS.
            $css = $theme->get_css_content_debug('scss', null, null);
            $this->assertStringContainsString('.ct-login-grid', $css);
            $this->assertStringContainsString('.ct-course-card', $css);
            $this->assertStringContainsString('--ct-color-primary:', $css);
            $this->assertStringContainsString('--ct-radius-lg:', $css);
            $this->assertStringContainsString('[data-bs-theme="dark"]', $css);
        }
    }

    /**
     * Only the login image, in the system context, is ever served.
     *
     * @param string $filearea
     * @param bool $systemcontext
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('pluginfile_provider')]
    public function test_pluginfile_rejects_other_areas(string $filearea, bool $systemcontext): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $context = $systemcontext ? \context_system::instance() : \context_course::instance($course->id);

        $this->expectException(\moodle_exception::class);
        // The 404 header cannot be sent under PHPUnit (output has started); only the refusal matters here.
        @theme_customboost_pluginfile($course, null, $context, $filearea, ['0', 'x.jpg'], false);
    }

    /**
     * File areas and contexts that must not be served.
     *
     * @return array
     */
    public static function pluginfile_provider(): array {
        return [
            'other area, system context' => ['preset', true],
            'backup area, system context' => ['backup', true],
            'login image, course context' => ['loginimage', false],
            'traversal attempt' => ['../loginimage', true],
        ];
    }
}
