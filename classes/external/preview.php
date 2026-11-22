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

namespace theme_customboost\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use theme_customboost\options;
use theme_customboost\tokens;

/**
 * CSS for the live preview of the appearance settings, computed exactly as the stylesheet would be.
 *
 * Read only: nothing is saved. Only for administrators of the site.
 *
 * @package    theme_customboost
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class preview extends external_api {
    /** @var string The preview panels; the CSS never reaches anything else on the page. */
    private const SCOPE = '.ct-preview [data-ct-preview-mode="%s"]';

    /**
     * Parameters: the unsaved values of the appearance settings.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'palette' => new external_value(PARAM_ALPHA, 'Palette'),
            'brandcolour' => new external_value(PARAM_RAW, 'Brand colour (custom palette)', VALUE_DEFAULT, ''),
            'accentcolour' => new external_value(PARAM_RAW, 'Accent colour (custom palette)', VALUE_DEFAULT, ''),
            'fontfamily' => new external_value(PARAM_ALPHA, 'Typeface', VALUE_DEFAULT, ''),
            'fontsize' => new external_value(PARAM_ALPHANUM, 'Text size', VALUE_DEFAULT, ''),
            'radius' => new external_value(PARAM_ALPHA, 'Corner radius', VALUE_DEFAULT, ''),
            'density' => new external_value(PARAM_ALPHA, 'Density', VALUE_DEFAULT, ''),
        ]);
    }

    /**
     * Compute the preview CSS.
     *
     * @param string $palette
     * @param string $brandcolour
     * @param string $accentcolour
     * @param string $fontfamily
     * @param string $fontsize
     * @param string $radius
     * @param string $density
     * @return array
     */
    public static function execute(
        string $palette,
        string $brandcolour = '',
        string $accentcolour = '',
        string $fontfamily = '',
        string $fontsize = '',
        string $radius = '',
        string $density = ''
    ): array {
        $params = self::validate_parameters(self::execute_parameters(), compact(
            'palette',
            'brandcolour',
            'accentcolour',
            'fontfamily',
            'fontsize',
            'radius',
            'density'
        ));
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('moodle/site:config', $context);

        // Every value goes through the same validation as the saved settings: an unknown one becomes the default and a
        // colour that is not a hex code is ignored, so nothing from the request reaches the CSS unchecked.
        $settings = (object) $params;
        $palettes = options::palettes($settings);
        [$sm, $md, $lg, $xl, $control, $button, $pill] = options::RADII[options::get('radius', $settings)];
        $density = options::DENSITIES[options::get('density', $settings)];
        $zoom = (float) options::SIZES[options::get('fontsize', $settings)] / 100;

        $light = sprintf(self::SCOPE, 'light');
        $dark = sprintf(self::SCOPE, 'dark');
        $css = tokens::to_css($palettes, $light, $dark, false);

        $shared = "    font-family: " . options::FONTS[options::get('fontfamily', $settings)] . ";\n";
        $shared .= "    --ct-font-family: " . options::FONTS[options::get('fontfamily', $settings)] . ";\n";
        $shared .= "    zoom: " . round($zoom, 4) . ";\n";
        $shared .= "    --ct-radius-sm: {$sm};\n    --ct-radius-md: {$md};\n    --ct-radius-lg: {$lg};\n";
        $shared .= "    --ct-radius-xl: {$xl};\n    --ct-radius-pill: {$pill};\n    --ct-density: {$density};\n";
        // The spacing tokens are computed where they are declared (:root), so they are declared again here.
        foreach ([1 => .25, 2 => .5, 3 => .75, 4 => 1, 5 => 1.5, 6 => 2, 7 => 3, 8 => 4] as $step => $rem) {
            $shared .= "    --ct-space-{$step}: calc({$rem}rem * {$density});\n";
        }
        $css .= "{$light},\n{$dark} {\n{$shared}}\n";
        foreach ([$light, $dark] as $scope) {
            $css .= "{$scope} .btn {\n    --bs-btn-border-radius: {$button};\n}\n";
            $css .= "{$scope} .alert {\n    --bs-alert-border-radius: {$md};\n}\n";
        }

        $adjusted = array_values(array_intersect(['primary-light', 'accent-light'], $palettes['adjusted']));
        return [
            'css' => $css,
            'adjusted' => !empty($adjusted),
            'notice' => $adjusted ? get_string('colouradjusted', 'theme_customboost') : '',
            'lightprimary' => $palettes['light']['primary'],
            'darkprimary' => $palettes['dark']['primary'],
            'lightaccent' => $palettes['light']['accent'],
            'darkaccent' => $palettes['dark']['accent'],
        ];
    }

    /**
     * Returned structure.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'css' => new external_value(PARAM_RAW, 'CSS scoped to the preview panels'),
            'adjusted' => new external_value(PARAM_BOOL, 'Whether the chosen colours were adjusted for contrast'),
            'notice' => new external_value(PARAM_TEXT, 'Explanation when they were'),
            'lightprimary' => new external_value(PARAM_RAW, 'Brand colour in use, light preset'),
            'darkprimary' => new external_value(PARAM_RAW, 'Brand colour in use, dark preset'),
            'lightaccent' => new external_value(PARAM_RAW, 'Accent colour in use, light preset'),
            'darkaccent' => new external_value(PARAM_RAW, 'Accent colour in use, dark preset'),
        ]);
    }
}
