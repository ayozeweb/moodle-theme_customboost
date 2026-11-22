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
 * Tests for the setting choices and what they become in the stylesheet.
 *
 * @package    theme_customboost
 * @category   test
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(options::class)]
final class options_test extends \basic_testcase {
    /**
     * Every option falls back to its default when missing, empty or invalid.
     */
    public function test_get_validates(): void {
        foreach (array_keys(options::DEFAULTS) as $name) {
            $this->assertSame(options::DEFAULTS[$name], options::get($name, (object) []), "$name missing");
            if ($name !== 'dashboardcardelements') {
                $this->assertSame(options::DEFAULTS[$name], options::get($name, (object) [$name => '']), "$name empty");
                $this->assertSame(options::DEFAULTS[$name], options::get($name, (object) [$name => 'x;}<b>']), "$name bad");
            }
            foreach (options::choices($name) as $choice) {
                $this->assertSame($choice, options::get($name, (object) [$name => $choice]));
            }
        }
    }

    /**
     * Card elements: a subset of the known ones, possibly empty, unknown ones dropped.
     */
    public function test_card_elements(): void {
        $this->assertSame('', options::get('dashboardcardelements', (object) ['dashboardcardelements' => '']));
        $this->assertSame('progress,continue', options::get(
            'dashboardcardelements',
            (object) ['dashboardcardelements' => 'progress,evil,continue']
        ));
    }

    /**
     * A palette gives its own colours; only the custom palette reads the colour pickers.
     */
    public function test_colours(): void {
        $pickers = ['brandcolour' => '#123456', 'accentcolour' => '#654321'];
        $this->assertSame(options::PALETTES['ocean'], options::colours((object) (['palette' => 'ocean'] + $pickers)));
        $this->assertSame(['#123456', '#654321'], options::colours((object) (['palette' => 'custom'] + $pickers)));
        $this->assertSame(
            [tokens::DEFAULT_BRAND, tokens::DEFAULT_ACCENT],
            options::colours((object) ['palette' => 'custom', 'brandcolour' => 'nope'])
        );
    }

    /**
     * Every ready-made palette is used unchanged in the light preset and reaches AA in both presets (ADM-V7).
     */
    public function test_palettes_reach_aa(): void {
        foreach (array_keys(options::PALETTES) as $palette) {
            $palettes = options::palettes((object) ['palette' => $palette]);
            $this->assertSame([], tokens::audit($palettes['light']), "$palette light");
            $this->assertSame([], tokens::audit($palettes['dark']), "$palette dark");
            $this->assertSame([], tokens::audit($palettes['light-high'], true), "$palette high contrast light");
            $this->assertSame([], tokens::audit($palettes['dark-high'], true), "$palette high contrast dark");
            $this->assertNotContains('primary-light', $palettes['adjusted'], "$palette brand changed in light");
            $this->assertNotContains('accent-light', $palettes['adjusted'], "$palette accent changed in light");
        }
    }

    /**
     * Typography, radius and density reach the SCSS; text size reaches the CSS only when it is not the default.
     */
    public function test_scss_and_css(): void {
        $scss = options::pre_scss((object) ['fontfamily' => 'serif', 'radius' => 'square', 'density' => 'compact']);
        $this->assertStringContainsString('$font-family-sans-serif: "Iowan Old Style"', $scss);
        $this->assertStringContainsString('$btn-border-radius: 0;', $scss);
        $this->assertStringContainsString('$ct-radius-lg: 0;', $scss);
        $this->assertStringContainsString('$ct-density: .85;', $scss);

        $scss = options::pre_scss((object) ['radius' => 'extra']);
        $this->assertStringContainsString('$btn-border-radius: 50rem;', $scss);

        $this->assertSame('', options::extra_css((object) ['fontsize' => '15']));
        $this->assertStringContainsString('font-size: 120%', options::extra_css((object) ['fontsize' => '18']));
    }
}
