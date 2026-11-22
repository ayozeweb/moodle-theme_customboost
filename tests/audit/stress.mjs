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
 * Stress conditions that the page audit does not cover: text at 200 %, WCAG 1.4.12 text spacing, zoom at 200 %,
 * landscape phones and the fixed header on short screens.
 *
 * TEST SITES ONLY. Uses the same pages.json and demo users as audit.mjs.
 *
 *   node stress.mjs --base=http://localhost:8080 --mode=light [--only=login,dashboard] [--conditions=text200,spacing]
 *
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {chromium} from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import {fileURLToPath} from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const args = Object.fromEntries(process.argv.slice(2).map((a) => {
    const [k, ...v] = a.replace(/^--/, '').split('=');
    return [k, v.length ? v.join('=') : true];
}));
const base = (args.base || 'http://localhost:8080').replace(/\/$/, '');
const mode = args.mode || 'light';
const defaultPages = 'login,dashboard,mycourses,course,book,assign,quiz,forum,calendar,profile,grades-user,admin-theme';
const only = (args.only || defaultPages).split(',');
const passwords = {
    ana: process.env.AUDIT_PASSWORD_ANA || 'Demo1234!',
    prof: process.env.AUDIT_PASSWORD_PROF || 'Demo1234!',
    admin: process.env.AUDIT_PASSWORD_ADMIN || null,
};
const outDir = path.join(here, 'results', 'stress-' + mode);
fs.mkdirSync(outDir, {recursive: true});

// WCAG 1.4.12: content must survive these spacings without loss.
const SPACING_CSS = `* { line-height: 1.5 !important; letter-spacing: .12em !important; word-spacing: .16em !important; }
p { margin-bottom: 2em !important; }`;

/** @type {Object<string, object>} Each condition: the browser context and what to do once the page has loaded. */
const conditions = {
    // A phone with the system text size at 200 %: every rem doubles (px sizes do not, as in Chrome's text scaling).
    text200: {viewport: {width: 360, height: 800}, isMobile: true, hasTouch: true, setup: async(page) => {
        await page.evaluate(() => {
            const size = parseFloat(getComputedStyle(document.documentElement).fontSize);
            document.documentElement.style.setProperty('font-size', (size * 2) + 'px', 'important');
        });
    }},
    spacing360: {viewport: {width: 360, height: 800}, isMobile: true, hasTouch: true, css: SPACING_CSS},
    spacing1280: {viewport: {width: 1280, height: 800}, css: SPACING_CSS},
    // A 1280 x 800 screen at 200 % zoom (the 400 % case is the 320 px run of audit.mjs).
    zoom200: {viewport: {width: 640, height: 400}},
    // Phones turned sideways: iPhone 15 and a compact Android.
    landscape852: {viewport: {width: 852, height: 393}, isMobile: true, hasTouch: true},
    landscape740: {viewport: {width: 740, height: 360}, isMobile: true, hasTouch: true},
};
const selected = args.conditions ? args.conditions.split(',') : Object.keys(conditions);

/**
 * Runs in the page: overflow, clipped text and how much of the screen the fixed header takes.
 *
 * @returns {object}
 */
function measure() {
    const doc = document.documentElement;
    const overflow = doc.scrollWidth - doc.clientWidth;
    const clipped = [];
    const describe = (el) => el.tagName.toLowerCase() + (el.id ? '#' + el.id : '') +
        (el.classList.length ? '.' + [...el.classList].slice(0, 3).join('.') : '');
    for (const el of document.body.querySelectorAll('*')) {
        const s = getComputedStyle(el);
        if (s.display === 'none' || s.visibility === 'hidden' || el.closest('.visually-hidden, .visually-hidden-focusable, .sr-only, .accesshide, [aria-hidden=true]')) {
            continue;
        }
        // Scrolling boxes keep their content reachable; only hidden or clipped overflow loses it.
        if (!['hidden', 'clip'].includes(s.overflowX) && !['hidden', 'clip'].includes(s.overflowY)) {
            continue;
        }
        const ownText = [...el.childNodes].some((n) => n.nodeType === 3 && n.textContent.trim());
        if (!ownText) {
            continue;
        }
        const r = el.getBoundingClientRect();
        if (!r.width || !r.height) {
            continue;
        }
        // Hidden with the visually-hidden mixin on an ancestor: a 1 px box.
        let tiny = false;
        for (let a = el.parentElement; a && a !== document.body && !tiny; a = a.parentElement) {
            const ar = a.getBoundingClientRect();
            tiny = ar.width <= 1 || ar.height <= 1;
        }
        if (tiny) {
            continue;
        }
        const x = el.scrollWidth - el.clientWidth;
        const y = el.scrollHeight - el.clientHeight;
        if (x > 1 || y > 1) {
            clipped.push(`${describe(el)} "${el.textContent.trim().slice(0, 30)}" (${x}x${y}px${
                s.textOverflow === 'ellipsis' ? ', ellipsis' : ''})`);
        }
    }
    const header = document.querySelector('.navbar.fixed-top, nav.navbar');
    const headerShare = header ? Math.round(header.getBoundingClientRect().height / window.innerHeight * 100) : 0;
    return {overflow, clipped: clipped.slice(0, 8), clippedCount: clipped.length, headerShare};
}

const pages = JSON.parse(fs.readFileSync(path.join(here, 'pages.json'), 'utf8')).filter((p) => only.includes(p.id));
const browser = await chromium.launch();
const sessions = {};

/**
 * Log a user in once and keep the cookies.
 *
 * @param {string} user
 * @returns {Promise<object|undefined|null>}
 */
async function storageFor(user) {
    if (user === 'anon') {
        return undefined;
    }
    if (sessions[user] === undefined) {
        if (!passwords[user]) {
            sessions[user] = null;
            return null;
        }
        const context = await browser.newContext();
        const page = await context.newPage();
        await page.goto(base + '/login/index.php');
        await page.fill('#username', user);
        await page.fill('#password', passwords[user]);
        await Promise.all([page.waitForNavigation(), page.click('#loginbtn')]);
        sessions[user] = await context.storageState();
        await context.close();
    }
    return sessions[user];
}

const results = [];
for (const name of selected) {
    const condition = conditions[name];
    for (const p of pages) {
        const storage = await storageFor(p.user);
        if (storage === null) {
            continue;
        }
        const context = await browser.newContext({
            viewport: condition.viewport,
            isMobile: !!condition.isMobile,
            hasTouch: !!condition.hasTouch,
            reducedMotion: 'reduce',
            storageState: storage,
        });
        const page = await context.newPage();
        const entry = {condition: name, id: p.id};
        try {
            await page.goto(base + p.url, {waitUntil: 'networkidle', timeout: 60000});
            if (condition.css) {
                await page.addStyleTag({content: condition.css});
            }
            if (condition.setup) {
                await condition.setup(page);
            }
            await page.waitForTimeout(300);
            Object.assign(entry, await page.evaluate(measure));
            await page.screenshot({path: path.join(outDir, `${p.id}-${name}.png`), fullPage: false});
        } catch (e) {
            entry.error = e.message.split('\n')[0];
        }
        await context.close();
        results.push(entry);
        console.log(`${name} ${p.id}: overflow ${entry.overflow ?? '?'}px, clipped ${entry.clippedCount ?? '?'}, ` +
            `header ${entry.headerShare ?? '?'}%${entry.error ? ' ERROR ' + entry.error : ''}`);
    }
}
await browser.close();

const lines = ['# Stress conditions · ' + mode, '', '| Condition | Page | Overflow | Clipped text | Header | Details |',
    '|---|---|---|---|---|---|'];
for (const r of results) {
    lines.push(`| ${r.condition} | ${r.id} | ${r.overflow ?? '–'} px | ${r.clippedCount ?? '–'} | ${r.headerShare ?? '–'} % | ${
        (r.error || (r.clipped || []).join('<br>')).replace(/\|/g, '\\|')} |`);
}
fs.writeFileSync(path.join(outDir, 'report.md'), lines.join('\n') + '\n');
fs.writeFileSync(path.join(outDir, 'report.json'), JSON.stringify(results, null, 2));
console.log('Report: ' + path.join(outDir, 'report.md'));
