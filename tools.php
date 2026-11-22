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
 * Theme tools: contrast report, export, import and restore of the settings.
 *
 * @package    theme_customboost
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use theme_customboost\form\import_form;
use theme_customboost\local\settings_io;
use theme_customboost\options;
use theme_customboost\tokens;

$action = optional_param('action', '', PARAM_ALPHA);

admin_externalpage_setup('theme_customboost_tools');
$url = new \core\url('/theme/customboost/tools.php');
$PAGE->set_url($url);
$settingsurl = new \core\url('/admin/settings.php', ['section' => 'themesettingcustomboost']);

// Export: a download, no page.
if ($action === 'export') {
    require_sesskey();
    $filename = 'theme_customboost-settings-' . date('Ymd-His') . '.json';
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('X-Content-Type-Options: nosniff');
    echo json_encode(settings_io::export(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    die();
}

// Restore the defaults, after a confirmation.
if ($action === 'restore') {
    echo $OUTPUT->header();
    echo $OUTPUT->confirm(
        get_string('restoreconfirm', 'theme_customboost'),
        new \core\url($url, ['action' => 'restoreconfirmed', 'sesskey' => sesskey()]),
        $url
    );
    echo $OUTPUT->footer();
    die();
}
if ($action === 'restoreconfirmed') {
    require_sesskey();
    $changed = settings_io::restore_defaults();
    redirect(
        $url,
        get_string('restoredone', 'theme_customboost', count($changed)),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// Import.
$form = new import_form($url);
$importresult = null;
$importerror = null;
if ($form->get_data()) {
    try {
        $importresult = settings_io::import($form->get_json());
    } catch (moodle_exception $e) {
        $importerror = $e->getMessage();
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('tools', 'theme_customboost'));
echo html_writer::tag('p', html_writer::link($settingsurl, get_string('backtosettings', 'theme_customboost')));

if ($importerror) {
    echo $OUTPUT->notification($importerror, \core\output\notification::NOTIFY_ERROR);
}
if ($importresult) {
    $type = $importresult['rejected'] ? \core\output\notification::NOTIFY_WARNING : \core\output\notification::NOTIFY_SUCCESS;
    $summary = get_string('importdone', 'theme_customboost', (object) [
        'applied' => count($importresult['applied']),
        'unchanged' => count($importresult['unchanged']),
        'rejected' => count($importresult['rejected']),
        'unknown' => count($importresult['unknown']),
    ]);
    $details = '';
    foreach ($importresult['rejected'] as $name => $reason) {
        $details .= html_writer::tag('li', s($name) . ': ' . s($reason));
    }
    foreach ($importresult['unknown'] as $name) {
        $details .= html_writer::tag('li', get_string('importunknown', 'theme_customboost', s($name)));
    }
    echo $OUTPUT->notification($summary . ($details ? html_writer::tag('ul', $details) : ''), $type);
}

// Contrast report of the colours in use.
echo $OUTPUT->heading(get_string('contrastreport', 'theme_customboost'), 3, 'mt-5');
$palettes = options::palettes();
$failures = 0;
$table = new html_table();
$table->head = [
    get_string('contrastforeground', 'theme_customboost'),
    get_string('contrastbackground', 'theme_customboost'),
    get_string('preset_light', 'theme_customboost'),
    get_string('preset_dark', 'theme_customboost'),
    get_string('preset_highcontrast', 'theme_customboost'),
    get_string('preset_highcontrastdark', 'theme_customboost'),
    get_string('contrastminimum', 'theme_customboost'),
];
$table->attributes['class'] = 'generaltable table-sm';
// The same pairs in every palette: 4.5:1 (AA) in light and dark, 7:1 (AAA) in the high contrast ones.
$pairs = tokens::contrast_pairs();
$highpairs = tokens::contrast_pairs(true);
foreach ($pairs as $i => [$fg, $bg, $min]) {
    $cells = [s($fg), s($bg)];
    foreach (tokens::MODES as $mode) {
        $high = str_ends_with($mode, '-high');
        $minimum = $high ? $highpairs[$i][2] : $min;
        $ratio = tokens::contrast($palettes[$mode][$fg], $palettes[$mode][$bg]);
        $ok = $ratio >= $minimum;
        $failures += $ok ? 0 : 1;
        $cells[] = sprintf('%.2f:1 ', $ratio) . html_writer::span(
            get_string($ok ? ($high ? 'contrastpasshigh' : 'contrastpass') : 'contrastfail', 'theme_customboost'),
            'badge ' . ($ok ? 'bg-success text-white' : 'bg-danger text-white')
        );
    }
    $cells[] = sprintf('%.1f:1 / %.1f:1', $min, $highpairs[$i][2]);
    $table->data[] = $cells;
}
echo $OUTPUT->notification(
    $failures
        ? get_string('contrastsummaryfail', 'theme_customboost', $failures)
        : get_string('contrastsummarypass', 'theme_customboost', count(tokens::MODES) * count($pairs)),
    $failures ? \core\output\notification::NOTIFY_ERROR : \core\output\notification::NOTIFY_SUCCESS,
    false
);
print_collapsible_region(
    html_writer::div(html_writer::table($table), 'table-responsive'),
    '',
    'ct-contrast-table',
    get_string('contrastdetails', 'theme_customboost'),
    '',
    true
);

// Export.
echo $OUTPUT->heading(get_string('export', 'theme_customboost'), 3, 'mt-5');
echo html_writer::tag('p', get_string('export_desc', 'theme_customboost'));
echo $OUTPUT->single_button(
    new \core\url($url, ['action' => 'export', 'sesskey' => sesskey()]),
    get_string('exportdownload', 'theme_customboost'),
    'post'
);

// Import.
echo $OUTPUT->heading(get_string('import', 'theme_customboost'), 3, 'mt-5');
echo html_writer::tag('p', get_string('import_desc', 'theme_customboost'));
$form->display();

// Restore.
echo $OUTPUT->heading(get_string('restore', 'theme_customboost'), 3, 'mt-5');
echo html_writer::tag('p', get_string('restore_desc', 'theme_customboost'));
echo $OUTPUT->single_button(
    new \core\url($url, ['action' => 'restore']),
    get_string('restorebutton', 'theme_customboost'),
    'get'
);

echo $OUTPUT->footer();
