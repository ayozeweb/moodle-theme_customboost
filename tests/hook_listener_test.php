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
 * Tests for the colour preset hook.
 *
 * @package    theme_customboost
 * @category   test
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\theme_customboost\hook_listener::class)]
final class hook_listener_test extends \advanced_testcase {
    /**
     * The preset falls back to light when unset or invalid.
     */
    public function test_get_preset(): void {
        $this->resetAfterTest();
        $this->assertSame('light', hook_listener::get_preset());
        set_config('colourpreset', 'dark', 'theme_customboost');
        $this->assertSame('dark', hook_listener::get_preset());
        set_config('colourpreset', 'sepia', 'theme_customboost');
        $this->assertSame('light', hook_listener::get_preset());
    }

    /**
     * With Boost colour modes off, the dark preset writes data-bs-theme="dark"; with them on, Boost decides.
     */
    public function test_html_attributes(): void {
        global $PAGE;
        $this->resetAfterTest();
        $PAGE->force_theme('customboost');
        set_config('colourpreset', 'dark', 'theme_customboost');

        $hook = new \core\hook\output\before_html_attributes($PAGE->get_renderer('core'));
        hook_listener::before_html_attributes($hook);
        $this->assertSame('dark', $hook->get_attributes()['data-bs-theme'] ?? null);

        set_config('enablecolourmodes', 1, 'theme_boost');
        $hook = new \core\hook\output\before_html_attributes($PAGE->get_renderer('core'));
        hook_listener::before_html_attributes($hook);
        $this->assertArrayNotHasKey('data-ct-preset', $hook->get_attributes());
    }
}
