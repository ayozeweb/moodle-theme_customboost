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

/**
 * Library functions for theme_customboost.
 *
 * @package    theme_customboost
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use theme_customboost\options;
use theme_customboost\tokens;

/**
 * Build the palettes from the theme settings.
 *
 * @param theme_config|null $theme
 * @return array
 */
function theme_customboost_palettes(?theme_config $theme = null): array {
    return options::palettes($theme ? $theme->settings : null);
}

/**
 * Main SCSS: Boost's default preset (variables, Bootstrap, Moodle) followed by this theme's layers.
 *
 * @param theme_config $theme
 * @return string
 */
function theme_customboost_get_main_scss_content($theme): string {
    global $CFG;
    $scss = file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    $scss .= "\n" . file_get_contents(__DIR__ . '/scss/customboost.scss');
    return $scss;
}

/**
 * SCSS prepended before Boost: Bootstrap variables derived from the tokens, so compiled components match them.
 *
 * @param theme_config $theme
 * @return string
 */
function theme_customboost_get_pre_scss($theme): string {
    $palettes = theme_customboost_palettes($theme);
    $light = $palettes['light'];
    $dark = $palettes['dark'];

    $scss = "\$primary: {$light['primary']};\n";
    $scss .= "\$success: {$light['success']};\n";
    // Not $warning: core uses it mostly as a fill under dark text (.bg-warning.text-dark), which needs Boost's yellow.
    // The theme's warning text colour reaches .text-warning through the custom property instead (_components.scss).
    $scss .= "\$danger: {$light['danger']};\n";
    $scss .= "\$info: {$light['info']};\n";
    // Boost derives its dark surfaces from these; aligning them keeps core components and tokens on one palette.
    $scss .= "\$dark-surface-raised: {$dark['surface']};\n";
    $scss .= "\$dark-surface-page: {$dark['bg']};\n";
    $scss .= "\$dark-surface-recessed: {$dark['surface-sunken']};\n";
    // Typography, corner radius and density chosen in the settings.
    $scss .= options::pre_scss($theme->settings);
    $scss .= file_get_contents(__DIR__ . '/scss/preset/variables.scss');

    if (!empty($theme->settings->scsspre)) {
        $scss .= "\n" . $theme->settings->scsspre;
    }
    return $scss;
}

/**
 * Extra SCSS appended last: the colour tokens (plain CSS) and the administrator's raw SCSS.
 *
 * @param theme_config $theme
 * @return string
 */
function theme_customboost_get_extra_scss($theme): string {
    $content = tokens::to_css(theme_customboost_palettes($theme));
    $content .= options::extra_css($theme->settings);

    if (!empty($theme->settings->scss)) {
        $content .= "\n" . $theme->settings->scss;
    }
    return $content;
}

/**
 * Precompiled CSS used while the SCSS is being compiled for the first time.
 *
 * @return string
 */
function theme_customboost_get_precompiled_css(): string {
    global $CFG;
    $file = __DIR__ . '/style/moodle.css';
    return file_exists($file) ? file_get_contents($file) : file_get_contents($CFG->dirroot . '/theme/boost/style/moodle.css');
}

/**
 * After the login image is saved: scale it down to at most 2000px wide and reset the theme caches.
 *
 * The login page is the first page every user loads, often on a phone. A 2000px image covers a 1000px wide panel at
 * twice the pixel density; anything bigger only costs bandwidth. GIF and SVG are not accepted by the setting; AVIF
 * and WebP are left as they are when the server's GD cannot read them.
 *
 * @param string $name The full setting name.
 * @return void
 */
function theme_customboost_process_login_image(string $name = ''): void {
    $maxwidth = 2000;
    $fs = get_file_storage();
    $context = context_system::instance();
    foreach ($fs->get_area_files($context->id, 'theme_customboost', 'loginimage', 0, 'id', false) as $file) {
        $info = $file->get_imageinfo();
        $resizable = $info && in_array($info['mimetype'], ['image/jpeg', 'image/png', 'image/webp'], true);
        if (!$resizable || $info['width'] <= $maxwidth) {
            continue;
        }
        try {
            $record = [
                'contextid' => $context->id,
                'component' => 'theme_customboost',
                'filearea' => 'loginimage',
                'itemid' => 0,
                'filepath' => $file->get_filepath(),
                'filename' => 'resized-' . $file->get_filename(),
            ];
            $resized = $fs->convert_image($record, $file, $maxwidth, null, true, 85);
            // Same name as before, so the stored setting value (the file path) stays valid.
            $filename = $file->get_filename();
            $file->delete();
            $resized->rename($resized->get_filepath(), $filename);
        } catch (Exception $e) {
            // Keep the original image: a slow login page is better than a missing one.
            debugging('Could not resize the login image: ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }
    theme_reset_all_caches();
}

/**
 * Serve the theme setting files.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function theme_customboost_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    if ($context->contextlevel == CONTEXT_SYSTEM && in_array($filearea, ['loginimage', 'logodark', 'logocompactdark'], true)) {
        $theme = theme_config::load('customboost');
        if (!array_key_exists('cacheability', $options)) {
            $options['cacheability'] = 'public';
        }
        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    }
    send_file_not_found();
}
