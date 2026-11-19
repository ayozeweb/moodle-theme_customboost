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

namespace theme_customboost\local;

/**
 * Export, import and restore of the theme settings.
 *
 * The list of settings is read from the settings tree itself and every value goes through the setting's own
 * write_setting(), so this class needs no list of its own and applies exactly the validation of the settings page.
 * Files (login image, dark logos) are not exported, imported or restored.
 *
 * @package    theme_customboost
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class settings_io {
    /** @var string Format marker of an export. */
    public const FORMAT = 'theme_customboost-settings';

    /** @var int Version of the export format. */
    public const FORMAT_VERSION = 1;

    /** @var int Largest export accepted, in bytes. */
    public const MAX_SIZE = 262144;

    /**
     * The settings that can be exported: stored values only, no files, headings or the preview.
     *
     * @return \admin_setting[] Keyed by setting name.
     */
    public static function settings(): array {
        $page = admin_get_root()->locate('themesettingcustomboost');
        if (!$page) {
            throw new \coding_exception('The theme settings page is not in the admin tree');
        }
        $settings = [];
        foreach ($page->settings as $setting) {
            if (
                $setting->nosave || $setting instanceof \admin_setting_configstoredfile
                    || $setting->plugin !== 'theme_customboost'
            ) {
                continue;
            }
            $settings[$setting->name] = $setting;
        }
        ksort($settings);
        return $settings;
    }

    /**
     * Current settings as an export document.
     *
     * @return array
     */
    public static function export(): array {
        $values = [];
        foreach (self::settings() as $name => $setting) {
            $value = get_config('theme_customboost', $name);
            $values[$name] = $value === false ? null : (string) $value;
        }
        return [
            'format' => self::FORMAT,
            'formatversion' => self::FORMAT_VERSION,
            'release' => \core_plugin_manager::instance()->get_plugin_info('theme_customboost')->release ?? null,
            'exported' => date('c'),
            'settings' => $values,
        ];
    }

    /**
     * Apply an export document.
     *
     * @param string $json The document.
     * @return array ['applied' => names, 'unchanged' => names, 'rejected' => name => reason, 'unknown' => names]
     * @throws \moodle_exception When the document is not an export of this theme.
     */
    public static function import(string $json): array {
        if (strlen($json) > self::MAX_SIZE) {
            throw new \moodle_exception('importtoolarge', 'theme_customboost');
        }
        $document = json_decode($json, true);
        if (
            !is_array($document) || ($document['format'] ?? null) !== self::FORMAT
                || !isset($document['settings']) || !is_array($document['settings'])
        ) {
            throw new \moodle_exception('importinvalid', 'theme_customboost');
        }

        $settings = self::settings();
        $result = ['applied' => [], 'unchanged' => [], 'rejected' => [], 'unknown' => []];
        foreach ($document['settings'] as $name => $value) {
            if (!is_string($name) || !isset($settings[$name])) {
                $result['unknown'][] = (string) $name;
                continue;
            }
            if ($value === null) {
                continue;
            }
            if (!is_scalar($value)) {
                $result['rejected'][$name] = get_string('importnotscalar', 'theme_customboost');
                continue;
            }
            self::write($settings[$name], (string) $value, $result);
        }
        theme_reset_all_caches();
        return $result;
    }

    /**
     * Put every exportable setting back to its default.
     *
     * @return string[] Names of the settings that changed.
     */
    public static function restore_defaults(): array {
        $result = ['applied' => [], 'unchanged' => [], 'rejected' => [], 'unknown' => []];
        foreach (self::settings() as $setting) {
            $default = $setting->get_defaultsetting();
            if (is_array($default)) {
                $default = implode(',', array_keys(array_filter($default)));
            }
            self::write($setting, (string) $default, $result);
        }
        theme_reset_all_caches();
        return $result['applied'];
    }

    /**
     * Write one value through the setting, then check that it was really stored.
     *
     * Some settings ignore an invalid value silently (a select outside its choices), so the stored value is read back.
     *
     * @param \admin_setting $setting
     * @param string $value The value as it is stored in the config.
     * @param array $result Collects the outcome.
     */
    protected static function write(\admin_setting $setting, string $value, array &$result): void {
        $name = $setting->name;
        $multi = $setting instanceof \admin_setting_configmulticheckbox;
        $normalise = function ($stored) use ($multi): string {
            $stored = (string) $stored;
            if (!$multi) {
                return $stored;
            }
            $items = array_filter(explode(',', $stored), 'strlen');
            sort($items);
            return implode(',', $items);
        };

        $before = get_config('theme_customboost', $name);
        if ($before !== false && $normalise($before) === $normalise($value)) {
            $result['unchanged'][] = $name;
            return;
        }
        $data = $multi ? array_fill_keys(array_filter(explode(',', $value), 'strlen'), 1) : $value;
        $error = $setting->write_setting($data);
        $after = get_config('theme_customboost', $name);
        if ($error !== '') {
            $result['rejected'][$name] = $error;
        } else if ($after === false || $normalise($after) !== $normalise($value)) {
            $result['rejected'][$name] = get_string('importnotaccepted', 'theme_customboost');
        } else {
            $result['applied'][] = $name;
        }
    }
}
