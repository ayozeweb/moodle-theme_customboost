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

use core\hook\output\before_html_attributes;
use core\hook\output\before_standard_head_html_generation;
use core\output\html_writer;
use theme_boost\colour_mode;

/**
 * Output hooks for theme_customboost.
 *
 * Boost 5.3 owns the colour mode when its experimental colour modes are enabled (each user picks light, dark or auto).
 * When they are disabled, this theme applies the colour preset chosen by the administrator instead, through the same
 * data-bs-theme attribute, so that Boost's own dark palette and this theme's tokens switch together.
 *
 * @package    theme_customboost
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_listener {
    /** @var string Light preset. */
    public const PRESET_LIGHT = 'light';
    /** @var string Dark preset. */
    public const PRESET_DARK = 'dark';
    /** @var string Follow the operating system. */
    public const PRESET_AUTO = 'auto';

    /**
     * Whether the page is rendered with this theme.
     *
     * @return bool
     */
    protected static function is_active(): bool {
        global $PAGE;
        if (during_initial_install()) {
            return false;
        }
        $theme = $PAGE->theme;
        return $theme->name === 'customboost' || in_array('customboost', $theme->parents, true);
    }

    /**
     * The preset configured by the administrator.
     *
     * @return string
     */
    public static function get_preset(): string {
        $preset = get_config('theme_customboost', 'colourpreset');
        return in_array($preset, [self::PRESET_LIGHT, self::PRESET_DARK, self::PRESET_AUTO], true) ? $preset : self::PRESET_LIGHT;
    }

    /**
     * Whether this theme, rather than Boost, decides the colour mode on this request.
     *
     * @return bool
     */
    protected static function theme_controls_mode(): bool {
        return self::is_active() && !colour_mode::is_enabled();
    }

    /**
     * Write data-bs-theme for the configured preset.
     *
     * @param before_html_attributes $hook
     */
    public static function before_html_attributes(before_html_attributes $hook): void {
        if (!self::theme_controls_mode()) {
            return;
        }
        $preset = self::get_preset();
        // Auto renders light until the inline script in the head resolves it, so it degrades gracefully without JS.
        $hook->add_attribute('data-bs-theme', $preset === self::PRESET_DARK ? 'dark' : 'light');
        $hook->add_attribute('data-ct-preset', $preset);
    }

    /**
     * Add the theme-color meta tags and, for the auto preset, the resolver script.
     *
     * @param before_standard_head_html_generation $hook
     */
    public static function before_standard_head_html_generation(before_standard_head_html_generation $hook): void {
        if (!self::is_active()) {
            return;
        }
        $palettes = tokens::build(
            get_config('theme_customboost', 'brandcolour') ?: null,
            get_config('theme_customboost', 'accentcolour') ?: null
        );
        // Mobile browsers tint their toolbar with this, so it matches the compact header in each mode.
        $hook->add_html(html_writer::empty_tag('meta', ['name' => 'theme-color',
            'content' => $palettes['light']['header-bg'], 'media' => '(prefers-color-scheme: light)']));
        $hook->add_html(html_writer::empty_tag('meta', ['name' => 'theme-color',
            'content' => $palettes['dark']['header-bg'], 'media' => '(prefers-color-scheme: dark)']));

        if (!self::theme_controls_mode() || self::get_preset() !== self::PRESET_AUTO || defined('BEHAT_SITE_RUNNING')) {
            return;
        }
        $js = "(function(){var r=document.documentElement,q=window.matchMedia('(prefers-color-scheme: dark)');" .
            "var s=function(){r.setAttribute('data-bs-theme',q.matches?'dark':'light');};s();" .
            "q.addEventListener('change',s);})();";
        $hook->add_html(html_writer::script($js));
    }
}
