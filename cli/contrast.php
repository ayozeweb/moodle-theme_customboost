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
 * Print the palettes in use and check every token pair against WCAG 2.1 AA.
 *
 * Usage: php theme/customboost/cli/contrast.php [--brand=#hex] [--accent=#hex]
 * Exit code 0 when everything passes, 1 otherwise.
 *
 * @package    theme_customboost
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');

use theme_customboost\tokens;

[$options, $unrecognised] = cli_get_params(['brand' => null, 'accent' => null, 'help' => false], ['h' => 'help']);
if ($options['help']) {
    cli_writeln('php theme/customboost/cli/contrast.php [--brand=#hex] [--accent=#hex]');
    exit(0);
}

$brand = $options['brand'] ?? (get_config('theme_customboost', 'brandcolour') ?: null);
$accent = $options['accent'] ?? (get_config('theme_customboost', 'accentcolour') ?: null);
$palettes = tokens::build($brand, $accent);

$failures = 0;
foreach (['light', 'dark'] as $mode) {
    cli_heading($mode);
    cli_writeln('| Foreground | Background | Ratio | Min | Result |');
    cli_writeln('|---|---|---|---|---|');
    foreach (tokens::contrast_pairs() as [$fg, $bg, $min]) {
        $ratio = tokens::contrast($palettes[$mode][$fg], $palettes[$mode][$bg]);
        $ok = $ratio >= $min;
        $failures += $ok ? 0 : 1;
        cli_writeln(sprintf(
            '| %s (%s) | %s (%s) | %.2f | %.1f | %s |',
            $fg,
            $palettes[$mode][$fg],
            $bg,
            $palettes[$mode][$bg],
            $ratio,
            $min,
            $ok ? 'AA' : 'FAIL'
        ));
    }
}
exit($failures ? 1 : 0);
