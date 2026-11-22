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
 * Live preview of the appearance settings of theme_customboost.
 *
 * Reads the unsaved values of the settings form, asks the server for the CSS they would produce (the same code that
 * builds the stylesheet, so contrast adjustments are never duplicated here) and applies it to the preview panels.
 *
 * @module     theme_customboost/preview
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import {getString} from 'core/str';

/** @type {string[]} Settings that the preview follows. */
const FIELDS = ['palette', 'brandcolour', 'accentcolour', 'fontfamily', 'fontsize', 'radius', 'density'];

/** @type {number} Milliseconds to wait after the last change before asking the server. */
const DELAY = 250;

/**
 * The form control of a setting.
 *
 * @param {string} name Setting name without the plugin prefix.
 * @returns {HTMLElement|null}
 */
const field = (name) => document.getElementById(`id_s_theme_customboost_${name}`);

/**
 * Current values of the followed settings.
 *
 * @returns {Object}
 */
const readValues = () => {
    const values = {};
    FIELDS.forEach((name) => {
        const element = field(name);
        if (element) {
            values[name] = element.value;
        }
    });
    return values;
};

/**
 * Initialise the preview.
 *
 * @param {string} selector The preview container.
 */
export const init = (selector) => {
    const root = document.querySelector(selector);
    if (!root || !field('palette')) {
        return;
    }
    // The CSS goes in the head: a style element inside the settings form would not be valid HTML.
    const style = document.createElement('style');
    style.dataset.region = 'ct-preview-style';
    document.head.append(style);
    const status = root.querySelector('[data-region="ct-preview-status"]');
    let timer = null;
    let last = '';

    const refresh = async() => {
        const values = readValues();
        const key = JSON.stringify(values);
        if (key === last) {
            return;
        }
        last = key;
        root.classList.add('ct-preview-loading');
        try {
            const result = await Ajax.call([{methodname: 'theme_customboost_preview', args: values}])[0];
            // Only apply the answer to the latest request.
            if (key === last) {
                style.textContent = result.css;
                status.textContent = result.notice;
            }
        } catch (error) {
            status.textContent = await getString('preview_failed', 'theme_customboost');
        } finally {
            root.classList.remove('ct-preview-loading');
        }
    };

    const schedule = () => {
        clearTimeout(timer);
        timer = setTimeout(refresh, DELAY);
    };

    // The colour picker writes into its text field without always firing an event, so clicks are followed too.
    const form = field('palette').closest('form') || document;
    ['change', 'input', 'keyup', 'click'].forEach((type) => form.addEventListener(type, schedule));
    refresh();
};
