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

use theme_customboost\privacy\provider;

/**
 * Plugin level checks: privacy provider and language files.
 *
 * @package    theme_customboost
 * @category   test
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(provider::class)]
final class plugin_test extends \basic_testcase {
    /**
     * The theme declares that it stores no personal data, with an existing reason string.
     */
    public function test_privacy_provider(): void {
        $this->assertInstanceOf(\core_privacy\local\metadata\null_provider::class, new provider());
        $this->assertSame('privacy:metadata', provider::get_reason());
        $this->assertTrue(get_string_manager()->string_exists(provider::get_reason(), 'theme_customboost'));
    }

    /**
     * English and Spanish define exactly the same strings, none of them empty.
     */
    public function test_language_files_match(): void {
        $load = function (string $lang): array {
            $string = [];
            include(__DIR__ . "/../lang/{$lang}/theme_customboost.php");
            return $string;
        };
        $en = $load('en');
        $es = $load('es');

        $this->assertSame([], array_diff(array_keys($en), array_keys($es)), 'Missing in Spanish');
        $this->assertSame([], array_diff(array_keys($es), array_keys($en)), 'Missing in English');
        foreach ([$en, $es] as $strings) {
            foreach ($strings as $key => $value) {
                $this->assertNotSame('', trim($value), "Empty string: $key");
            }
        }
    }
}
