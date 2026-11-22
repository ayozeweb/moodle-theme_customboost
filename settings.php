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
 * Six tabs. Everything that changes the look (colours, logos, typography, shape) is on the first one, under a live
 * preview, so that the preview can follow every change. The choices themselves live in classes/options.php.
 *
 * @package    theme_customboost
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// The tools page (contrast report, export, import, restore) sits next to the settings under Appearance > Themes.
$ADMIN->add('themes', new admin_externalpage(
    'theme_customboost_tools',
    get_string('toolspage', 'theme_customboost'),
    new \core\url('/theme/customboost/tools.php'),
    'moodle/site:config'
));

use theme_customboost\admin\setting_preview;
use theme_customboost\hook_listener;
use theme_customboost\options;
use theme_customboost\tokens;

if ($ADMIN->fulltree) {
    $component = 'theme_customboost';
    $str = fn(string $id, $a = null) => get_string($id, $component, $a);
    // Every setting that changes the stylesheet recompiles it when saved.
    $css = function (admin_setting $setting) use (&$page): void {
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
    };
    $choices = fn(string $option, string $prefix) => array_combine(
        options::choices($option),
        array_map(fn($value) => get_string($prefix . $value, 'theme_customboost'), options::choices($option))
    );

    $settings = new theme_boost_admin_settingspage_tabs('themesettingcustomboost', $str('configtitle'));

    // 1. Appearance: preview, colours, logos, typography and shape.
    $page = new admin_settingpage('theme_customboost_appearance', $str('appearance'));

    $page->add(new setting_preview('theme_customboost/preview', $str('preview'), $str('preview_desc')));

    $page->add(new admin_setting_heading('theme_customboost/colours', $str('colours'), ''));

    $css(new admin_setting_configselect(
        'theme_customboost/colourpreset',
        $str('colourpreset'),
        $str('colourpreset_desc'),
        hook_listener::PRESET_LIGHT,
        [
            hook_listener::PRESET_LIGHT => $str('preset_light'),
            hook_listener::PRESET_DARK => $str('preset_dark'),
            hook_listener::PRESET_AUTO => $str('preset_auto'),
            hook_listener::PRESET_HIGH_LIGHT => $str('preset_highcontrast'),
            hook_listener::PRESET_HIGH_DARK => $str('preset_highcontrastdark'),
        ]
    ));

    // The colours really in use after the AA adjustment, with a warning when the chosen ones had to change.
    $palettes = options::palettes();
    $inuse = $str('effectivecolours', (object) [
        'light' => $palettes['light']['primary'],
        'dark' => $palettes['dark']['primary'],
        'accentlight' => $palettes['light']['accent'],
        'accentdark' => $palettes['dark']['accent'],
    ]);
    if (array_intersect(['primary-light', 'accent-light'], $palettes['adjusted'])) {
        $inuse .= ' ' . $str('colouradjusted');
    }
    $css(new admin_setting_configselect(
        'theme_customboost/palette',
        $str('palette'),
        $str('palette_desc') . ' ' . $inuse,
        options::DEFAULTS['palette'],
        $choices('palette', 'palette_')
    ));
    $css(new admin_setting_configcolourpicker(
        'theme_customboost/brandcolour',
        $str('brandcolour'),
        $str('brandcolour_desc'),
        tokens::DEFAULT_BRAND
    ));
    $css(new admin_setting_configcolourpicker(
        'theme_customboost/accentcolour',
        $str('accentcolour'),
        $str('accentcolour_desc'),
        tokens::DEFAULT_ACCENT
    ));
    // Dependencies go on the tabs page, not the tab: theme_boost_admin_settingspage_tabs copies a tab's settings up but
    // not its dependencies, and admin/settings.php only reads those of the top level page.
    $settings->hide_if('theme_customboost/brandcolour', 'theme_customboost/palette', 'neq', options::PALETTE_CUSTOM);
    $settings->hide_if('theme_customboost/accentcolour', 'theme_customboost/palette', 'neq', options::PALETTE_CUSTOM);

    $page->add(new admin_setting_heading('theme_customboost/logos', $str('logos'), $str('logos_desc')));
    $logotypes = ['maxfiles' => 1, 'accepted_types' => ['.svg', '.png', '.webp', '.jpg', '.jpeg']];
    $css(new admin_setting_configstoredfile(
        'theme_customboost/logodark',
        $str('logodark'),
        $str('logodark_desc'),
        'logodark',
        0,
        $logotypes
    ));
    $css(new admin_setting_configstoredfile(
        'theme_customboost/logocompactdark',
        $str('logocompactdark'),
        $str('logocompactdark_desc'),
        'logocompactdark',
        0,
        $logotypes
    ));

    $page->add(new admin_setting_heading('theme_customboost/typography', $str('typography'), ''));
    $css(new admin_setting_configselect(
        'theme_customboost/fontfamily',
        $str('fontfamily'),
        $str('fontfamily_desc'),
        options::DEFAULTS['fontfamily'],
        $choices('fontfamily', 'font_')
    ));
    $css(new admin_setting_configselect(
        'theme_customboost/fontsize',
        $str('fontsize'),
        $str('fontsize_desc'),
        options::DEFAULTS['fontsize'],
        $choices('fontsize', 'fontsize_')
    ));

    $page->add(new admin_setting_heading('theme_customboost/shape', $str('shape'), ''));
    $css(new admin_setting_configselect(
        'theme_customboost/radius',
        $str('radius'),
        $str('radius_desc'),
        options::DEFAULTS['radius'],
        $choices('radius', 'radius_')
    ));
    $css(new admin_setting_configselect(
        'theme_customboost/density',
        $str('density'),
        $str('density_desc'),
        options::DEFAULTS['density'],
        $choices('density', 'density_')
    ));

    $settings->add($page);

    // 2. Header.
    $page = new admin_settingpage('theme_customboost_header', $str('header'));
    $page->add(new admin_setting_configcheckbox(
        'theme_customboost/showsitename',
        $str('showsitename'),
        $str('showsitename_desc'),
        0
    ));
    $settings->add($page);

    // 3. Login page.
    $page = new admin_settingpage('theme_customboost_login', $str('loginpage'));
    $css(new admin_setting_configselect(
        'theme_customboost/loginlayout',
        $str('loginlayout'),
        $str('loginlayout_desc'),
        options::DEFAULTS['loginlayout'],
        $choices('loginlayout', 'loginlayout_')
    ));
    $setting = new admin_setting_configstoredfile(
        'theme_customboost/loginimage',
        $str('loginimage'),
        $str('loginimage_desc'),
        'loginimage',
        0,
        ['maxfiles' => 1, 'accepted_types' => ['.jpg', '.jpeg', '.png', '.webp', '.avif'], 'maxbytes' => 1048576]
    );
    // Large photos are scaled down when saved: the login page is the first thing every user loads.
    $setting->set_updatedcallback('theme_customboost_process_login_image');
    $page->add($setting);
    $page->add(new admin_setting_configtext(
        'theme_customboost/logintitle',
        $str('logintitle'),
        $str('logintitle_desc'),
        '',
        PARAM_TEXT
    ));
    $page->add(new admin_setting_configtextarea(
        'theme_customboost/logintagline',
        $str('logintagline'),
        $str('logintagline_desc'),
        '',
        PARAM_TEXT
    ));
    $page->add(new admin_setting_confightmleditor(
        'theme_customboost/loginfootertext',
        $str('loginfootertext'),
        $str('loginfootertext_desc'),
        ''
    ));
    $settings->add($page);

    // 4. Personal area.
    $page = new admin_settingpage('theme_customboost_dashboard', $str('dashboard'));
    $page->add(new admin_setting_configcheckbox(
        'theme_customboost/dashboardcourses',
        $str('dashboardcourses'),
        $str('dashboardcourses_desc'),
        1
    ));
    $page->add(new admin_setting_configselect(
        'theme_customboost/dashboardsource',
        $str('dashboardsource'),
        $str('dashboardsource_desc'),
        options::DEFAULTS['dashboardsource'],
        $choices('dashboardsource', 'dashboardsource_')
    ));
    $page->add(new admin_setting_configselect(
        'theme_customboost/dashboardcoursecount',
        $str('dashboardcoursecount'),
        '',
        6,
        [3 => 3, 4 => 4, 6 => 6, 8 => 8, 12 => 12]
    ));
    $page->add(new admin_setting_configmulticheckbox(
        'theme_customboost/dashboardcardelements',
        $str('dashboardcardelements'),
        $str('dashboardcardelements_desc'),
        array_fill_keys(explode(',', options::DEFAULTS['dashboardcardelements']), 1),
        $choices('dashboardcardelements', 'cardelement_')
    ));
    $page->add(new admin_setting_configtext(
        'theme_customboost/dashboardgreeting',
        $str('dashboardgreeting'),
        $str('dashboardgreeting_desc'),
        '',
        PARAM_TEXT
    ));
    $page->add(new admin_setting_configtext(
        'theme_customboost/dashboardsubtitle',
        $str('dashboardsubtitle'),
        $str('dashboardsubtitle_desc'),
        '',
        PARAM_TEXT
    ));
    $dependent = ['dashboardsource', 'dashboardcoursecount', 'dashboardcardelements', 'dashboardgreeting', 'dashboardsubtitle'];
    foreach ($dependent as $name) {
        $settings->hide_if('theme_customboost/' . $name, 'theme_customboost/dashboardcourses');
    }
    $settings->add($page);

    // 5. Footer.
    $page = new admin_settingpage('theme_customboost_footer', $str('footer'));
    $page->add(new admin_setting_confightmleditor(
        'theme_customboost/footertext',
        $str('footertext'),
        $str('footertext_desc'),
        ''
    ));
    $settings->add($page);

    // 6. Advanced.
    $page = new admin_settingpage('theme_customboost_advanced', $str('advancedsettings'));
    $page->add(new admin_setting_description(
        'theme_customboost/toolslink',
        $str('tools'),
        $str('tools_desc', (new \core\url('/theme/customboost/tools.php'))->out())
    ));
    $css(new admin_setting_scsscode(
        'theme_customboost/scsspre',
        $str('rawscsspre'),
        $str('rawscsspre_desc'),
        '',
        PARAM_RAW
    ));
    $css(new admin_setting_scsscode(
        'theme_customboost/scss',
        $str('rawscss'),
        $str('rawscss_desc'),
        '',
        PARAM_RAW
    ));
    $settings->add($page);
}
