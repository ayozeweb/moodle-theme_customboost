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
 * Tests for the design token builder: every preset must reach WCAG 2.1 AA.
 *
 * @package    theme_customboost
 * @category   test
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \theme_customboost\tokens
 */
final class tokens_test extends \basic_testcase {
    /**
     * Brand/accent pairs, including hostile ones (white, black, pure yellow, neon green).
     *
     * @return array
     */
    public static function brand_provider(): array {
        return [
            'defaults' => [null, null],
            'moodle blue' => ['#0f6cbf', '#f98012'],
            'yellow' => ['#ffcc00', '#ffee00'],
            'neon green' => ['#39ff14', '#00ffff'],
            'white' => ['#ffffff', '#ffffff'],
            'black' => ['#000000', '#000000'],
            'red' => ['#e11d48', '#7c3aed'],
            'mid grey' => ['#777777', '#888888'],
            'short hex' => ['#06c', '#f60'],
            'invalid falls back' => ['not-a-colour', 'rgb(1,2,3)'],
        ];
    }

    /**
     * Both presets pass every contrast pair for any brand colour.
     *
     * @dataProvider brand_provider
     * @param string|null $brand
     * @param string|null $accent
     */
    public function test_presets_reach_aa(?string $brand, ?string $accent): void {
        $palettes = tokens::build($brand, $accent);
        $this->assertSame([], tokens::audit($palettes['light']), 'Light preset');
        $this->assertSame([], tokens::audit($palettes['dark']), 'Dark preset');
    }

    /**
     * A brand that already passes is kept as it is in the light preset.
     */
    public function test_accessible_brand_is_kept(): void {
        $palettes = tokens::build('#0f6cbf');
        $this->assertSame('#0f6cbf', $palettes['light']['primary']);
        $this->assertNotContains('primary-light', $palettes['adjusted']);
    }

    /**
     * A brand without enough contrast is darkened, keeping its hue, and reported as adjusted.
     */
    public function test_light_brand_is_adjusted(): void {
        $palettes = tokens::build('#ffcc00');
        $this->assertNotSame('#ffcc00', $palettes['light']['primary']);
        $this->assertContains('primary-light', $palettes['adjusted']);
        [$h1] = tokens::rgb_to_hsl(tokens::hex_to_rgb('#ffcc00'));
        [$h2] = tokens::rgb_to_hsl(tokens::hex_to_rgb($palettes['light']['primary']));
        $this->assertEqualsWithDelta($h1, $h2, 0.02);
    }

    /**
     * Known WCAG reference values.
     */
    public function test_contrast_reference_values(): void {
        $this->assertEqualsWithDelta(21.0, tokens::contrast('#000000', '#ffffff'), 0.01);
        $this->assertEqualsWithDelta(1.0, tokens::contrast('#777777', '#777777'), 0.01);
        $this->assertEqualsWithDelta(4.48, tokens::contrast('#777777', '#ffffff'), 0.01);
    }

    /**
     * The CSS uses the shared namespace and covers both colour modes.
     */
    public function test_css_output(): void {
        $css = tokens::to_css(tokens::build());
        $this->assertStringContainsString(':root,', $css);
        $this->assertStringContainsString('[data-bs-theme="dark"]', $css);
        $this->assertStringContainsString('--ct-color-primary: #4338ca;', $css);
        $this->assertStringContainsString('--ct-color-primary-rgb: 67, 56, 202;', $css);
        $this->assertStringContainsString('color-scheme: dark;', $css);
    }

    /**
     * Hex normalisation.
     */
    public function test_normalise_hex(): void {
        $this->assertSame('#0066cc', tokens::normalise_hex('#06C'));
        $this->assertSame('#abcdef', tokens::normalise_hex('ABCDEF'));
        $this->assertNull(tokens::normalise_hex('red'));
        $this->assertNull(tokens::normalise_hex(null));
    }
}
