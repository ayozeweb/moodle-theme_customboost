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
    /** @var string High contrast, light: black on white, 7:1 text (WCAG AAA). */
    public const PRESET_HIGH_LIGHT = 'highcontrast';
    /** @var string High contrast, dark: white on black, 7:1 text (WCAG AAA). */
    public const PRESET_HIGH_DARK = 'highcontrastdark';
    /** @var string[] Every preset. */
    public const PRESETS = [
        self::PRESET_LIGHT,
        self::PRESET_DARK,
        self::PRESET_AUTO,
        self::PRESET_HIGH_LIGHT,
        self::PRESET_HIGH_DARK,
    ];

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
        return in_array($preset, self::PRESETS, true) ? $preset : self::PRESET_LIGHT;
    }

    /**
     * Colour mode of a preset: light, dark or auto.
     *
     * @param string $preset
     * @return string
     */
    public static function preset_mode(string $preset): string {
        return match ($preset) {
            self::PRESET_DARK, self::PRESET_HIGH_DARK => self::PRESET_DARK,
            self::PRESET_AUTO => self::PRESET_AUTO,
            default => self::PRESET_LIGHT,
        };
    }

    /**
     * Whether this request uses a high contrast preset. (The device's "more contrast" setting is applied by CSS.)
     *
     * @return bool
     */
    public static function high_contrast(): bool {
        return self::theme_controls_mode()
            && in_array(self::get_preset(), [self::PRESET_HIGH_LIGHT, self::PRESET_HIGH_DARK], true);
    }

    /**
     * The colour mode of this request as far as the server knows it: light, dark or auto (resolved in the browser).
     *
     * @return string
     */
    public static function current_mode(): string {
        if (colour_mode::is_enabled()) {
            return colour_mode::get_current_mode();
        }
        return self::preset_mode(self::get_preset());
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
        $hook->add_attribute('data-bs-theme', self::preset_mode($preset) === self::PRESET_DARK ? 'dark' : 'light');
        $hook->add_attribute('data-ct-preset', $preset);
        if (self::high_contrast()) {
            $hook->add_attribute('data-ct-contrast', 'more');
        }
    }

    /**
     * Add the theme-color meta tags, the keyboard refinements and, for the auto preset, the resolver script.
     *
     * @param before_standard_head_html_generation $hook
     */
    public static function before_standard_head_html_generation(before_standard_head_html_generation $hook): void {
        if (!self::is_active()) {
            return;
        }
        // Keyboard and focus refinements for drawers and the swipeable course row (amd/src/a11y.js).
        $hook->renderer->get_page()->requires->js_call_amd('theme_customboost/a11y', 'init');

        $palettes = options::palettes();
        // Mobile browsers tint their toolbar with this, so it matches the compact header. When the server knows the
        // mode, one colour: a dark site on a phone set to light must not get a white toolbar. Only the automatic mode
        // follows the device.
        $mode = self::current_mode();
        if ($mode === self::PRESET_LIGHT || $mode === self::PRESET_DARK) {
            $palette = $palettes[$mode . (self::high_contrast() ? '-high' : '')];
            $hook->add_html(html_writer::empty_tag('meta', ['name' => 'theme-color', 'content' => $palette['header-bg']]));
        } else {
            foreach ([self::PRESET_LIGHT, self::PRESET_DARK] as $scheme) {
                $hook->add_html(html_writer::empty_tag('meta', ['name' => 'theme-color',
                    'content' => $palettes[$scheme]['header-bg'], 'media' => "(prefers-color-scheme: {$scheme})"]));
            }
        }

        if (!self::theme_controls_mode() || self::get_preset() !== self::PRESET_AUTO || defined('BEHAT_SITE_RUNNING')) {
            return;
        }
        $js = "(function(){var r=document.documentElement,q=window.matchMedia('(prefers-color-scheme: dark)');" .
            "var s=function(){r.setAttribute('data-bs-theme',q.matches?'dark':'light');};s();" .
            "q.addEventListener('change',s);})();";
        $hook->add_html(html_writer::script($js));
    }
}
