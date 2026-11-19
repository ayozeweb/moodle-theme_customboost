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
 * The choices offered by the theme settings and what each one becomes in the stylesheet.
 *
 * One place for every option, so that the settings page, the SCSS callbacks, the live preview and the export/import
 * tool cannot disagree. Every reader validates: a value that is not one of the choices falls back to the default.
 *
 * @package    theme_customboost
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class options {
    /** @var string The palette that uses the brand and accent colour pickers. */
    public const PALETTE_CUSTOM = 'custom';

    /** @var array Ready-made palettes: brand and accent. Chosen so that the light preset uses them unchanged. */
    public const PALETTES = [
        // The accent is the AA-adjusted value of the 0.x default (#c2410c): the colour those sites already saw.
        'indigo' => [tokens::DEFAULT_BRAND, '#bd3f0c'],
        'ocean' => ['#0b6bcb', '#0f766e'],
        'forest' => ['#147c3b', '#975c07'],
        'garnet' => ['#9f1239', '#0f766e'],
        'graphite' => ['#334155', '#2060eb'],
    ];

    /** @var string The fallback shared by every font stack. */
    protected const FONT_FALLBACK = 'system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';

    /** @var array Font stacks. Inter ships with the theme, Noto Sans with Boost; the others use what the device has. */
    public const FONTS = [
        'inter' => '"Inter", ' . self::FONT_FALLBACK . ', "Noto Sans"',
        'noto' => '"Noto Sans", ' . self::FONT_FALLBACK,
        'system' => self::FONT_FALLBACK . ', "Noto Sans"',
        'serif' => '"Iowan Old Style", "Palatino Linotype", Palatino, Georgia, "Noto Serif", serif',
    ];

    /** @var array Body text size in px (Boost's 15px is the default) => root font size. Everything in rem scales. */
    public const SIZES = [
        '15' => '100%',
        '16' => '106.6667%',
        '17' => '113.3333%',
        '18' => '120%',
    ];

    /** @var array Corner radius: small, medium, large, extra large, controls, buttons, pills. */
    public const RADII = [
        'square' => ['0', '0', '0', '0', '0', '0', '0'],
        'soft' => ['.25rem', '.375rem', '.5rem', '.75rem', '.375rem', '.375rem', '999px'],
        'rounded' => ['.5rem', '.75rem', '1rem', '1.5rem', '.625rem', '.625rem', '999px'],
        'extra' => ['.75rem', '1rem', '1.5rem', '2rem', '1rem', '50rem', '999px'],
    ];

    /** @var array Spacing multiplier of the theme's own components. */
    public const DENSITIES = [
        'compact' => '.85',
        'normal' => '1',
        'comfortable' => '1.2',
    ];

    /** @var array Login page layouts. */
    public const LOGIN_LAYOUTS = ['left', 'right', 'cover', 'plain'];

    /** @var array Which courses the personal area cards show. */
    public const DASHBOARD_SOURCES = ['recent', 'inprogress', 'starred', 'all'];

    /** @var array Optional parts of a course card. */
    public const CARD_ELEMENTS = ['category', 'progress', 'enddate', 'continue'];

    /** @var array Defaults of the settings read through this class. */
    public const DEFAULTS = [
        'palette' => 'indigo',
        'fontfamily' => 'inter',
        'fontsize' => '15',
        'radius' => 'rounded',
        'density' => 'normal',
        'loginlayout' => 'left',
        'dashboardsource' => 'recent',
        'dashboardcardelements' => 'category,progress',
    ];

    /**
     * Read a setting, falling back to its default when it is missing or not one of the choices.
     *
     * @param string $name Setting name.
     * @param \stdClass|null $settings The theme settings (theme_config::$settings), or null to read the config.
     * @return string
     */
    public static function get(string $name, ?\stdClass $settings = null): string {
        $settings = $settings ?? get_config('theme_customboost');
        $value = isset($settings->$name) ? (string) $settings->$name : '';
        $choices = self::choices($name);
        if ($name === 'dashboardcardelements') {
            // A multi-checkbox: a comma separated subset, possibly empty.
            if (!isset($settings->$name)) {
                return self::DEFAULTS[$name];
            }
            return implode(',', array_intersect($choices, explode(',', $value)));
        }
        return in_array($value, $choices, true) ? $value : self::DEFAULTS[$name];
    }

    /**
     * The valid values of a setting.
     *
     * @param string $name
     * @return string[]
     */
    public static function choices(string $name): array {
        return match ($name) {
            'palette' => array_merge(array_keys(self::PALETTES), [self::PALETTE_CUSTOM]),
            'fontfamily' => array_keys(self::FONTS),
            'fontsize' => array_map('strval', array_keys(self::SIZES)),
            'radius' => array_keys(self::RADII),
            'density' => array_keys(self::DENSITIES),
            'loginlayout' => self::LOGIN_LAYOUTS,
            'dashboardsource' => self::DASHBOARD_SOURCES,
            'dashboardcardelements' => self::CARD_ELEMENTS,
            default => throw new \coding_exception('Unknown option ' . $name),
        };
    }

    /**
     * Brand and accent colours in use: the palette's, or the colour pickers' for the custom palette.
     *
     * @param \stdClass|null $settings
     * @return string[] [brand, accent], not yet adjusted for contrast.
     */
    public static function colours(?\stdClass $settings = null): array {
        $settings = $settings ?? get_config('theme_customboost');
        $palette = self::get('palette', $settings);
        if ($palette !== self::PALETTE_CUSTOM) {
            return self::PALETTES[$palette];
        }
        return [
            tokens::normalise_hex($settings->brandcolour ?? null) ?? tokens::DEFAULT_BRAND,
            tokens::normalise_hex($settings->accentcolour ?? null) ?? tokens::DEFAULT_ACCENT,
        ];
    }

    /**
     * The light and dark palettes for the settings.
     *
     * @param \stdClass|null $settings
     * @return array As tokens::build().
     */
    public static function palettes(?\stdClass $settings = null): array {
        [$brand, $accent] = self::colours($settings);
        return tokens::build($brand, $accent);
    }

    /**
     * SCSS variables for typography and shape, prepended before Boost and before scss/preset/variables.scss.
     *
     * @param \stdClass|null $settings
     * @return string
     */
    public static function pre_scss(?\stdClass $settings = null): string {
        $settings = $settings ?? get_config('theme_customboost');
        [$sm, $md, $lg, $xl, $control, $button, $pill] = self::RADII[self::get('radius', $settings)];

        $scss = '$font-family-sans-serif: ' . self::FONTS[self::get('fontfamily', $settings)] . ";\n";
        $scss .= "\$ct-density: " . self::DENSITIES[self::get('density', $settings)] . ";\n";
        $scss .= "\$ct-radius-sm: {$sm};\n\$ct-radius-md: {$md};\n\$ct-radius-lg: {$lg};\n\$ct-radius-xl: {$xl};\n";
        $scss .= "\$ct-radius-pill: {$pill};\n";
        // Bootstrap's own radii, so that core components follow the same shape.
        $scss .= "\$border-radius: {$control};\n\$border-radius-sm: {$sm};\n\$border-radius-lg: {$lg};\n";
        $scss .= "\$border-radius-xl: {$xl};\n\$card-border-radius: {$lg};\n\$input-border-radius: {$control};\n";
        $scss .= "\$btn-border-radius: {$button};\n\$btn-border-radius-sm: {$button};\n\$btn-border-radius-lg: {$button};\n";
        $scss .= "\$dropdown-border-radius: {$md};\n\$modal-content-border-radius: {$lg};\n";
        return $scss;
    }

    /**
     * Plain CSS appended at the end of the stylesheet.
     *
     * @param \stdClass|null $settings
     * @return string
     */
    public static function extra_css(?\stdClass $settings = null): string {
        $size = self::get('fontsize', $settings);
        if ($size === self::DEFAULTS['fontsize']) {
            return '';
        }
        // The root size scales every rem in Moodle, so text, controls and spacing grow together.
        return "html {\n    font-size: " . self::SIZES[$size] . ";\n}\n";
    }
}
