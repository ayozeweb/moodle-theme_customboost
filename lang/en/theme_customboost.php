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
 * English strings for theme_customboost.
 *
 * @package    theme_customboost
 * @category   string
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['accentcolour'] = 'Accent colour';
$string['accentcolour_desc'] = 'Secondary colour used for highlights. Only used with the "Custom" palette. It is adjusted automatically to reach WCAG 2.1 AA contrast in both colour presets.';
$string['advancedsettings'] = 'Advanced';
$string['allmycourses'] = 'All my courses';
$string['appearance'] = 'Appearance';
$string['backtosettings'] = 'Back to the theme settings';
$string['brandcolour'] = 'Brand colour';
$string['brandcolour_desc'] = 'Main colour of the theme: buttons, links, focus and active navigation. Only used with the "Custom" palette. When the chosen colour does not reach WCAG 2.1 AA contrast it is darkened (light preset) or lightened (dark preset) while keeping its hue.';
$string['cardelement_category'] = 'Category';
$string['cardelement_continue'] = '"Continue" button';
$string['cardelement_enddate'] = 'End date';
$string['cardelement_progress'] = 'Progress';
$string['cardenddate'] = 'Ends {$a}';
$string['choosereadme'] = 'Custom Boost is a mobile-first child theme of Boost with a login page with image, a personal area with course cards, a compact header on mobile and light and dark colour presets built on accessible design tokens.';
$string['colouradjusted'] = 'The chosen colours did not have enough contrast for text and were adjusted, keeping their hue. Choose a darker tone to stay closer to the original.';
$string['colourpreset'] = 'Colour preset';
$string['colourpreset_desc'] = 'Colour preset used by the site. When the experimental colour modes of Boost are enabled (Boost settings, Experimental tab), each user chooses their own mode and this setting is ignored.';
$string['colours'] = 'Colours';
$string['completed'] = 'Completed';
$string['configtitle'] = 'Custom Boost';
$string['continuecourse'] = 'Continue';
$string['contrastbackground'] = 'Background';
$string['contrastdetails'] = 'All combinations';
$string['contrastfail'] = 'Fails';
$string['contrastforeground'] = 'Text or element';
$string['contrastminimum'] = 'Minimum';
$string['contrastpass'] = 'AA';
$string['contrastreport'] = 'Contrast of the colours in use';
$string['contrastsummaryfail'] = '{$a} combinations do not reach WCAG 2.1 AA. This should not happen with the colour settings: check the raw SCSS of the Advanced tab.';
$string['contrastsummarypass'] = 'All {$a} combinations of text and background, in the light and the dark preset, reach WCAG 2.1 AA.';
$string['coursecategory'] = 'Category';
$string['dashboard'] = 'Personal area';
$string['dashboardcardelements'] = 'Card elements';
$string['dashboardcardelements_desc'] = 'Parts of each course card. The course image and name are always shown.';
$string['dashboardcoursecount'] = 'Number of course cards';
$string['dashboardcourses'] = 'Show course cards';
$string['dashboardcourses_desc'] = 'Show a welcome header and cards with the courses accessed most recently, with their progress, at the top of the personal area.';
$string['dashboardgreeting'] = 'Greeting';
$string['dashboardgreeting_desc'] = 'Heading of the personal area. {firstname} is replaced with the user\'s first name. Multi-language content is supported. Leave empty for "Hello, {firstname}".';
$string['dashboardsection_all'] = 'My courses';
$string['dashboardsection_inprogress'] = 'In progress';
$string['dashboardsection_recent'] = 'Continue learning';
$string['dashboardsection_starred'] = 'Starred courses';
$string['dashboardsource'] = 'Courses shown';
$string['dashboardsource_all'] = 'All my courses';
$string['dashboardsource_desc'] = 'Which courses the cards show. Except for "Recently accessed", they are ordered by last access.';
$string['dashboardsource_inprogress'] = 'In progress (started, not finished)';
$string['dashboardsource_recent'] = 'Recently accessed';
$string['dashboardsource_starred'] = 'Starred';
$string['dashboardsubtitle'] = 'Subtitle';
$string['dashboardsubtitle_desc'] = 'Line under the greeting. {firstname} is replaced with the user\'s first name. Leave empty for "Pick up where you left off."';
$string['density'] = 'Density';
$string['density_comfortable'] = 'Comfortable';
$string['density_compact'] = 'Compact';
$string['density_desc'] = 'Spacing inside the theme\'s own components (cards, header, login page). Controls keep their 44 px touch size.';
$string['density_normal'] = 'Normal';
$string['effectivecolours'] = 'Colours in use: {$a->light} (light) and {$a->dark} (dark); accent {$a->accentlight} (light) and {$a->accentdark} (dark).';
$string['export'] = 'Export';
$string['export_desc'] = 'Download every setting of the theme as a JSON file, to copy them to another site or keep a backup. The login image and the logos are files and are not included.';
$string['exportdownload'] = 'Download settings';
$string['font_inter'] = 'Inter (included with the theme)';
$string['font_noto'] = 'Noto Sans (included with Moodle)';
$string['font_serif'] = 'Serif (Georgia and similar)';
$string['font_system'] = 'Device font (system-ui)';
$string['fontfamily'] = 'Typeface';
$string['fontfamily_desc'] = 'Typeface of the whole site. All options are served from your server or already on the device: nothing is loaded from external services.';
$string['fontsize'] = 'Text size';
$string['fontsize_15'] = '15 px (Moodle default)';
$string['fontsize_16'] = '16 px';
$string['fontsize_17'] = '17 px';
$string['fontsize_18'] = '18 px';
$string['fontsize_desc'] = 'Size of body text. Headings, controls and spacing grow in proportion. Form fields never go below 16 px, so phones do not zoom in.';
$string['footer'] = 'Footer';
$string['footertext'] = 'Footer text';
$string['footertext_desc'] = 'Shown at the end of every page: legal notice, contact, links. Leave empty for no footer band.';
$string['greeting'] = 'Hello, {$a}';
$string['greeting_subtitle'] = 'Pick up where you left off.';
$string['header'] = 'Header';
$string['import'] = 'Import';
$string['import_desc'] = 'Apply a file exported from this tool. Each value is checked like on the settings page; unknown settings and invalid values are listed and skipped. Settings that are not in the file are left as they are.';
$string['importapply'] = 'Import settings';
$string['importdone'] = 'Import finished: {$a->applied} changed, {$a->unchanged} already had that value, {$a->rejected} rejected, {$a->unknown} unknown.';
$string['importfile'] = 'Export file';
$string['importinvalid'] = 'This is not a settings export of the Custom Boost theme.';
$string['importnotaccepted'] = 'value not accepted';
$string['importnothing'] = 'Choose a file or paste the content of one.';
$string['importnotscalar'] = 'the value must be text or a number';
$string['importtext'] = 'Or paste its content';
$string['importtoolarge'] = 'The file is too large to be a settings export.';
$string['importunknown'] = '{$a}: unknown setting, skipped';
$string['loginfootertext'] = 'Text under the form';
$string['loginfootertext_desc'] = 'Shown under the login form: help desk, conditions of use, privacy. Leave empty for none.';
$string['loginimage'] = 'Login image';
$string['loginimage_desc'] = 'Image shown next to the login form (on top of it on mobile). Landscape, at least 1600 px wide and up to 1 MB. JPG, PNG and WebP images wider than 2000 px are scaled down when saved. A default illustration is used when empty.';
$string['loginlayout'] = 'Layout';
$string['loginlayout_cover'] = 'Image covering the page, form in a card';
$string['loginlayout_desc'] = 'Arrangement of the login page on screens 992 px and wider. On phones and tablets the image is always a band above the form, except with "No image".';
$string['loginlayout_left'] = 'Image on the left';
$string['loginlayout_plain'] = 'No image';
$string['loginlayout_right'] = 'Image on the right';
$string['loginpage'] = 'Login page';
$string['logintagline'] = 'Login tagline';
$string['logintagline_default'] = 'Learn at your own pace, from any device.';
$string['logintagline_desc'] = 'Short text shown over the login image. Leave empty for the default text.';
$string['logintitle'] = 'Login title';
$string['logintitle_desc'] = 'Heading shown over the login image. Leave empty to use the site name.';
$string['logocompactdark'] = 'Compact logo for dark mode';
$string['logocompactdark_desc'] = 'Header logo used in dark mode, including the automatic preset. Requires the compact logo of Appearance > Logos.';
$string['logodark'] = 'Logo for dark mode';
$string['logodark_desc'] = 'Logo above the login form in dark mode. With the automatic preset the normal logo is used, because the mode is only known in the browser.';
$string['logos'] = 'Logos for dark mode';
$string['logos_desc'] = 'The normal logos are set in Site administration > Appearance > Logos. Upload a version for dark backgrounds if they do not read well on them.';
$string['nocourses'] = 'You are not enrolled in any course yet.';
$string['notstarted'] = 'Not started';
$string['palette'] = 'Palette';
$string['palette_custom'] = 'Custom (brand and accent colours below)';
$string['palette_desc'] = 'Ready-made colour combinations. Choose "Custom" to use your own brand and accent colours.';
$string['palette_forest'] = 'Forest (green and amber)';
$string['palette_garnet'] = 'Garnet (garnet and teal)';
$string['palette_graphite'] = 'Graphite (slate and blue)';
$string['palette_indigo'] = 'Indigo (indigo and orange)';
$string['palette_ocean'] = 'Ocean (blue and teal)';
$string['pluginname'] = 'Custom Boost';
$string['preset_auto'] = 'Automatic (follows the device)';
$string['preset_dark'] = 'Dark';
$string['preset_light'] = 'Light';
$string['preview'] = 'Preview';
$string['preview_alert'] = 'Your changes are not saved yet.';
$string['preview_button'] = 'Primary button';
$string['preview_category'] = 'Category';
$string['preview_course'] = 'Sample course';
$string['preview_dark'] = 'Dark';
$string['preview_desc'] = 'Updates as you change the settings on this tab, before saving. Both colour presets are shown.';
$string['preview_failed'] = 'The preview could not be updated. The settings can still be saved.';
$string['preview_light'] = 'Light';
$string['preview_link'] = 'A link in the text';
$string['preview_progress'] = '60% complete';
$string['preview_secondary'] = 'Secondary';
$string['privacy:metadata'] = 'The Custom Boost theme does not store any personal data.';
$string['progresspercent'] = '{$a}% complete';
$string['radius'] = 'Corner radius';
$string['radius_desc'] = 'Shape of buttons, fields, cards and windows across the site.';
$string['radius_extra'] = 'Very rounded (pill buttons)';
$string['radius_rounded'] = 'Rounded';
$string['radius_soft'] = 'Soft';
$string['radius_square'] = 'Square';
$string['rawscss'] = 'Raw SCSS';
$string['rawscss_desc'] = 'SCSS or CSS added at the end of the stylesheet.';
$string['rawscsspre'] = 'Raw initial SCSS';
$string['rawscsspre_desc'] = 'SCSS code added before everything else, typically to set Bootstrap variables.';
$string['region-side-pre'] = 'Right';
$string['restore'] = 'Restore defaults';
$string['restore_desc'] = 'Put every setting of the theme back to its default. The login image and the logos are kept.';
$string['restorebutton'] = 'Restore defaults';
$string['restoreconfirm'] = 'Every setting of the theme will go back to its default value (the login image and the logos are kept). Continue?';
$string['restoredone'] = 'Defaults restored: {$a} settings changed.';
$string['shape'] = 'Shape and density';
$string['showsitename'] = 'Show the site name next to the logo';
$string['showsitename_desc'] = 'With a compact logo, the header shows only the logo. Tick to show the short site name after it as well. Without a logo the name is always shown.';
$string['tools'] = 'Tools';
$string['tools_desc'] = 'Export or import these settings, restore the defaults and check colour contrast: <a href="{$a}">theme tools</a>.';
$string['toolspage'] = 'Custom Boost: tools';
$string['typography'] = 'Typography';
