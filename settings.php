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
 * Settings for theme_customboost.
 *
 * @package    theme_customboost
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use theme_customboost\hook_listener;
use theme_customboost\tokens;

if ($ADMIN->fulltree) {
    $settings = new theme_boost_admin_settingspage_tabs('themesettingcustomboost',
        get_string('configtitle', 'theme_customboost'));

    // Appearance: colour preset and brand colours.
    $page = new admin_settingpage('theme_customboost_appearance', get_string('appearance', 'theme_customboost'));

    $choices = [
        hook_listener::PRESET_LIGHT => get_string('preset_light', 'theme_customboost'),
        hook_listener::PRESET_DARK => get_string('preset_dark', 'theme_customboost'),
        hook_listener::PRESET_AUTO => get_string('preset_auto', 'theme_customboost'),
    ];
    $setting = new admin_setting_configselect('theme_customboost/colourpreset',
        get_string('colourpreset', 'theme_customboost'), get_string('colourpreset_desc', 'theme_customboost'),
        hook_listener::PRESET_LIGHT, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Show the colours actually in use, after the automatic AA adjustment.
    $palettes = tokens::build(get_config('theme_customboost', 'brandcolour') ?: null,
        get_config('theme_customboost', 'accentcolour') ?: null);
    $effective = get_string('effectivecolours', 'theme_customboost', (object) [
        'light' => $palettes['light']['primary'],
        'dark' => $palettes['dark']['primary'],
        'accentlight' => $palettes['light']['accent'],
        'accentdark' => $palettes['dark']['accent'],
    ]);

    $setting = new admin_setting_configcolourpicker('theme_customboost/brandcolour',
        get_string('brandcolour', 'theme_customboost'),
        get_string('brandcolour_desc', 'theme_customboost') . ' ' . $effective, tokens::DEFAULT_BRAND);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $setting = new admin_setting_configcolourpicker('theme_customboost/accentcolour',
        get_string('accentcolour', 'theme_customboost'), get_string('accentcolour_desc', 'theme_customboost'),
        tokens::DEFAULT_ACCENT);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $settings->add($page);

    // Login page.
    $page = new admin_settingpage('theme_customboost_login', get_string('loginpage', 'theme_customboost'));

    $setting = new admin_setting_configstoredfile('theme_customboost/loginimage',
        get_string('loginimage', 'theme_customboost'), get_string('loginimage_desc', 'theme_customboost'), 'loginimage', 0,
        ['maxfiles' => 1, 'accepted_types' => ['.jpg', '.jpeg', '.png', '.webp', '.avif']]);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $page->add(new admin_setting_configtext('theme_customboost/logintitle',
        get_string('logintitle', 'theme_customboost'), get_string('logintitle_desc', 'theme_customboost'), '', PARAM_TEXT));

    $page->add(new admin_setting_configtextarea('theme_customboost/logintagline',
        get_string('logintagline', 'theme_customboost'), get_string('logintagline_desc', 'theme_customboost'), '',
        PARAM_TEXT));

    $settings->add($page);

    // Dashboard.
    $page = new admin_settingpage('theme_customboost_dashboard', get_string('dashboard', 'theme_customboost'));

    $page->add(new admin_setting_configcheckbox('theme_customboost/dashboardcourses',
        get_string('dashboardcourses', 'theme_customboost'), get_string('dashboardcourses_desc', 'theme_customboost'), 1));

    $page->add(new admin_setting_configselect('theme_customboost/dashboardcoursecount',
        get_string('dashboardcoursecount', 'theme_customboost'), '', 6, [3 => 3, 4 => 4, 6 => 6, 8 => 8, 12 => 12]));

    $settings->add($page);

    // Advanced.
    $page = new admin_settingpage('theme_customboost_advanced', get_string('advancedsettings', 'theme_customboost'));

    $setting = new admin_setting_scsscode('theme_customboost/scsspre',
        get_string('rawscsspre', 'theme_customboost'), get_string('rawscsspre_desc', 'theme_customboost'), '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $setting = new admin_setting_scsscode('theme_customboost/scss',
        get_string('rawscss', 'theme_customboost'), get_string('rawscss_desc', 'theme_customboost'), '', PARAM_RAW);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $settings->add($page);
}
