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

use theme_customboost\local\settings_io;

/**
 * Tests for export, import and restore of the settings.
 *
 * @package    theme_customboost
 * @category   test
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(settings_io::class)]
final class settings_io_test extends \advanced_testcase {
    /**
     * Load the settings tree as an administrator.
     */
    protected function setUp(): void {
        global $CFG;
        parent::setUp();
        require_once($CFG->libdir . '/adminlib.php');
        $this->resetAfterTest();
        $this->setAdminUser();
    }

    /**
     * Exported: stored values only. Not exported: files, headings, the preview.
     */
    public function test_export(): void {
        $export = settings_io::export();
        $this->assertSame(settings_io::FORMAT, $export['format']);
        foreach (
            ['palette', 'brandcolour', 'colourpreset', 'fontfamily', 'radius', 'loginlayout', 'dashboardsource',
                'dashboardcardelements', 'footertext', 'scss'] as $name
        ) {
            $this->assertArrayHasKey($name, $export['settings'], $name);
        }
        foreach (['loginimage', 'logodark', 'logocompactdark', 'preview', 'colours', 'toolslink'] as $name) {
            $this->assertArrayNotHasKey($name, $export['settings'], $name);
        }
    }

    /**
     * A round trip restores the exported values.
     */
    public function test_round_trip(): void {
        set_config('palette', 'garnet', 'theme_customboost');
        set_config('dashboardcardelements', 'progress,continue', 'theme_customboost');
        $json = json_encode(settings_io::export());

        set_config('palette', 'ocean', 'theme_customboost');
        set_config('dashboardcardelements', 'category', 'theme_customboost');
        $result = settings_io::import($json);

        $this->assertContains('palette', $result['applied']);
        $this->assertContains('dashboardcardelements', $result['applied']);
        $this->assertSame([], $result['rejected']);
        $this->assertSame('garnet', get_config('theme_customboost', 'palette'));
        $elements = explode(',', get_config('theme_customboost', 'dashboardcardelements'));
        sort($elements);
        $this->assertSame(['continue', 'progress'], $elements);
    }

    /**
     * Invalid values, unknown and non scalar settings are reported and nothing else changes.
     */
    public function test_import_rejects(): void {
        set_config('radius', 'soft', 'theme_customboost');
        $result = settings_io::import(json_encode(['format' => settings_io::FORMAT, 'settings' => [
            'radius' => 'triangular',
            'brandcolour' => 'red;} body {display:none',
            'fontfamily' => ['inter'],
            'admin' => 'x',
            'palette' => 'forest',
        ]]));
        $this->assertArrayHasKey('radius', $result['rejected']);
        $this->assertArrayHasKey('brandcolour', $result['rejected']);
        $this->assertArrayHasKey('fontfamily', $result['rejected']);
        $this->assertSame(['admin'], $result['unknown']);
        $this->assertSame(['palette'], $result['applied']);
        $this->assertSame('soft', get_config('theme_customboost', 'radius'));
        $this->assertStringNotContainsString('display', (string) get_config('theme_customboost', 'brandcolour'));
    }

    /**
     * Documents that are not an export of this theme are refused as a whole.
     *
     * @param string $json
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('invalid_provider')]
    public function test_import_invalid_document(string $json): void {
        $this->expectException(\moodle_exception::class);
        settings_io::import($json);
    }

    /**
     * Invalid documents.
     *
     * @return array
     */
    public static function invalid_provider(): array {
        return [
            'not json' => ['palette=forest'],
            'other format' => ['{"format":"theme_other-settings","settings":{}}'],
            'no settings' => ['{"format":"theme_customboost-settings"}'],
            'too large' => ['{"format":"theme_customboost-settings","x":"' . str_repeat('a', settings_io::MAX_SIZE) . '"}'],
        ];
    }

    /**
     * Restore puts every exported setting back to its default.
     */
    public function test_restore_defaults(): void {
        set_config('palette', 'graphite', 'theme_customboost');
        set_config('fontsize', '18', 'theme_customboost');
        set_config('dashboardcardelements', '', 'theme_customboost');
        $changed = settings_io::restore_defaults();
        $this->assertContains('palette', $changed);
        $this->assertSame(options::DEFAULTS['palette'], get_config('theme_customboost', 'palette'));
        $this->assertSame(options::DEFAULTS['fontsize'], get_config('theme_customboost', 'fontsize'));
        $this->assertSame(options::DEFAULTS['dashboardcardelements'], get_config('theme_customboost', 'dashboardcardelements'));
    }

    /**
     * Settings forced in config.php are reported and left alone by import and restore.
     */
    public function test_forced_settings_are_skipped(): void {
        global $CFG, $DB;
        set_config('palette', 'ocean', 'theme_customboost');
        $CFG->forced_plugin_settings = ['theme_customboost' => ['palette' => 'forest']];
        \cache::make('core', 'config')->purge();

        $result = settings_io::import(json_encode(['format' => settings_io::FORMAT, 'settings' => [
            'palette' => 'garnet',
            'radius' => 'soft',
        ]]));
        $this->assertSame(get_string('configoverride', 'admin'), $result['rejected']['palette']);
        $this->assertSame(['radius'], $result['applied']);
        $this->assertNotContains('palette', settings_io::restore_defaults());
        $stored = $DB->get_field('config_plugins', 'value', ['plugin' => 'theme_customboost', 'name' => 'palette']);
        $this->assertSame('ocean', $stored);
        $this->assertSame('forest', get_config('theme_customboost', 'palette'));
    }
}
