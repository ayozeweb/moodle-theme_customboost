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

use theme_customboost\tokens;

/**
 * Build the palettes from the theme settings.
 *
 * @param theme_config|null $theme
 * @return array
 */
function theme_customboost_palettes(?theme_config $theme = null): array {
    $settings = $theme ? $theme->settings : get_config('theme_customboost');
    return tokens::build($settings->brandcolour ?? null, $settings->accentcolour ?? null);
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
    $scss .= "\$warning: {$light['warning']};\n";
    $scss .= "\$danger: {$light['danger']};\n";
    $scss .= "\$info: {$light['info']};\n";
    // Boost derives its dark surfaces from these; aligning them keeps core components and tokens on one palette.
    $scss .= "\$dark-surface-raised: {$dark['surface']};\n";
    $scss .= "\$dark-surface-page: {$dark['bg']};\n";
    $scss .= "\$dark-surface-recessed: {$dark['surface-sunken']};\n";
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
    if ($context->contextlevel == CONTEXT_SYSTEM && $filearea === 'loginimage') {
        $theme = theme_config::load('customboost');
        if (!array_key_exists('cacheability', $options)) {
            $options['cacheability'] = 'public';
        }
        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    }
    send_file_not_found();
}
