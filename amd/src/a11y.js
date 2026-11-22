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
 * Keyboard and focus refinements for phones and tablets.
 *
 * - A drawer that covers the page (below 992 px, with a backdrop) closes with Escape, keeps Tab inside it while open,
 *   and gives focus back to the button that opened it however it was closed (after a tap on the backdrop Boost
 *   focuses another drawer's button, or nothing).
 * - In the personal area's swipeable row, a focused course card is scrolled fully into view.
 *
 * @module     theme_customboost/a11y
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Drawers from 'theme_boost/drawers';

/** @type {MediaQueryList} Where Boost shows drawers over the page with a backdrop. */
const overlay = window.matchMedia('(max-width: 991.98px)');

/** @type {string} Elements that can take focus with Tab. */
const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]):not([type=hidden]), select:not([disabled]), ' +
    'textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

/**
 * The drawer open over the page, if any.
 *
 * @returns {HTMLElement|null}
 */
const openOverlayDrawer = () => (overlay.matches ? document.querySelector('.drawer.show') : null);

/**
 * Visible focusable elements inside a node.
 *
 * @param {HTMLElement} node
 * @returns {HTMLElement[]}
 */
const focusables = (node) => [...node.querySelectorAll(FOCUSABLE)].filter((el) => el.getClientRects().length);

/**
 * The visible button that opens a drawer.
 *
 * @param {string} id Drawer id.
 * @returns {HTMLElement|undefined}
 */
const opener = (id) => [...document.querySelectorAll(`[data-toggler="drawers"][data-target="${id}"]`)]
    .find((el) => !el.closest('.drawer') && el.getClientRects().length);

/**
 * Escape closes the drawer; Tab and Shift+Tab wrap around inside it.
 *
 * @param {KeyboardEvent} e
 */
const onKeydown = (e) => {
    const drawer = openOverlayDrawer();
    if (!drawer) {
        return;
    }
    if (e.key === 'Escape') {
        Drawers.getDrawerInstanceForNode(drawer)?.closeDrawer();
        e.preventDefault();
        return;
    }
    if (e.key !== 'Tab') {
        return;
    }
    const items = focusables(drawer);
    if (!items.length) {
        return;
    }
    const first = items[0];
    const last = items[items.length - 1];
    if (!drawer.contains(document.activeElement)) {
        first.focus();
        e.preventDefault();
    } else if (e.shiftKey && document.activeElement === first) {
        last.focus();
        e.preventDefault();
    } else if (!e.shiftKey && document.activeElement === last) {
        first.focus();
        e.preventDefault();
    }
};

/** @type {WeakSet<HTMLElement>} Drawers that had the focus when they started to close. */
const hadFocus = new WeakSet();

/**
 * Remember whether the focus is inside a drawer that is about to close, or was just lost.
 *
 * @param {CustomEvent} e
 */
const onDrawerHide = (e) => {
    // The backdrop closes every drawer, also those that were not open: only an open one gets the focus back. A tap on
    // the backdrop has already moved the focus to the body by now.
    const active = document.activeElement;
    if (e.target.classList.contains('show') && (!active || active === document.body || e.target.contains(active))) {
        hadFocus.add(e.target);
    }
};

/**
 * After a drawer hides, give focus back to the button that opens it when the focus was inside the drawer. Boost
 * does it for its close button, but after a tap on the backdrop it focuses another drawer's button, or nothing.
 *
 * @param {CustomEvent} e
 */
const onDrawerHidden = (e) => {
    const drawer = e.target;
    if (!hadFocus.has(drawer)) {
        return;
    }
    hadFocus.delete(drawer);
    // Boost moves focus itself after 300 ms: correct it once it is done.
    setTimeout(() => {
        const button = opener(drawer.id);
        if (button && document.activeElement !== button) {
            button.focus();
        }
    }, 350);
};

/**
 * A focused card in the swipeable row scrolls fully into view.
 *
 * @param {FocusEvent} e
 */
const onFocusin = (e) => {
    const card = e.target.closest?.('.ct-course-scroller .ct-course-card');
    if (!card) {
        return;
    }
    const scroller = card.closest('.ct-course-scroller');
    const box = scroller.getBoundingClientRect();
    const rect = card.getBoundingClientRect();
    if (rect.left < box.left || rect.right > box.right) {
        const smooth = !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        card.scrollIntoView({block: 'nearest', inline: 'start', behavior: smooth ? 'smooth' : 'auto'});
    }
};

let initialised = false;

/**
 * Initialise once per page.
 */
export const init = () => {
    if (initialised) {
        return;
    }
    initialised = true;
    document.addEventListener('keydown', onKeydown);
    document.addEventListener(Drawers.eventTypes.drawerHide, onDrawerHide);
    document.addEventListener(Drawers.eventTypes.drawerHidden, onDrawerHidden);
    document.addEventListener('focusin', onFocusin);
};
