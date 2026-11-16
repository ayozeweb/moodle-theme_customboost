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
 * Build the precompiled stylesheet (style/moodle.css) served while the SCSS is compiled for the first time.
 *
 * Run it when preparing a release, after any SCSS change:
 *   php theme/customboost/cli/build_css.php
 * It uses the default colours, whatever the site settings are.
 *
 * @package    theme_customboost
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');

$theme = theme_config::load('customboost');
// The fallback must not carry one site's branding.
foreach (['brandcolour', 'accentcolour', 'scsspre', 'scss'] as $setting) {
    unset($theme->settings->{$setting});
}

// Compile without the post-processing step: the file must keep the [[pix:...]] and [[font:...]] placeholders, which
// Moodle resolves to the URLs of each site when it serves the fallback. The public API only returns processed CSS.
$compile = new ReflectionMethod($theme, 'get_css_content_from_scss');
$css = $compile->invoke($theme, false);
if (!$css || strpos($css, '.ct-login-grid') === false) {
    cli_error('The compiled CSS does not contain the theme styles; not writing it.');
}

// Boost's own extra SCSS resolves its default login background to a URL of this site: turn it back into a placeholder.
$css = preg_replace(
    '~url\\(([\'"]?)' . preg_quote($CFG->wwwroot, '~') . '/theme/image\\.php/[^/]+/([a-z_]+)/[0-9-]+/([^\'")]+)\\1\\)~',
    'url([[pix:$2|$3]])',
    $css
);
if (strpos($css, $CFG->wwwroot) !== false) {
    cli_error('The compiled CSS still contains URLs of this site; not writing it.');
}

$dir = __DIR__ . '/../style';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}
file_put_contents($dir . '/moodle.css', $css);
cli_writeln('Written ' . realpath($dir . '/moodle.css') . ' (' . round(strlen($css) / 1024) . ' KB)');
