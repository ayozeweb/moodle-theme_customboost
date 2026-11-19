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

use theme_customboost\local\upgrade;

/**
 * Tests for the upgrade from 0.x: sites with their own colours keep them (ADM-V5).
 *
 * @package    theme_customboost
 * @category   test
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(upgrade::class)]
final class upgrade_test extends \advanced_testcase {
    /**
     * Changed colours select the custom palette; default colours leave the palette to its default.
     *
     * @param string|null $brand
     * @param string|null $accent
     * @param string|false $expected Palette after the upgrade (false: not set, the default applies).
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('colours_provider')]
    public function test_palette_migration(?string $brand, ?string $accent, string|false $expected): void {
        $this->resetAfterTest();
        unset_config('palette', 'theme_customboost');
        foreach (['brandcolour' => $brand, 'accentcolour' => $accent] as $name => $value) {
            $value === null ? unset_config($name, 'theme_customboost') : set_config($name, $value, 'theme_customboost');
        }
        upgrade::select_custom_palette();
        $this->assertSame($expected, get_config('theme_customboost', 'palette'));
        if ($brand !== null) {
            $this->assertSame($brand, get_config('theme_customboost', 'brandcolour'));
        }
    }

    /**
     * Colour combinations.
     *
     * @return array
     */
    public static function colours_provider(): array {
        return [
            'never set' => [null, null, false],
            'defaults' => ['#4338ca', '#c2410c', false],
            'defaults, other case' => ['#4338CA', '#C2410C', false],
            'brand changed' => ['#0f6cbf', '#c2410c', 'custom'],
            'accent changed' => ['#4338ca', '#00aa00', 'custom'],
        ];
    }
}
