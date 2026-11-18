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
 * Page audit for theme_customboost: horizontal overflow, touch target size, axe (WCAG 2.2 AA), JavaScript errors and
 * visual regression, for every page in pages.json at 360, 768 and 1280 px.
 *
 * TEST SITES ONLY. See tests/audit/README.md.
 *
 *   node audit.mjs --base=http://localhost:8080 --mode=light [--only=login,dashboard] [--widths=360]
 *                  [--update-baseline]
 *
 * The colour mode is not switched by this script: set the theme preset first (light or dark) and pass the same
 * value in --mode, which only names the output folders.
 *
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {chromium, request} from 'playwright';
import {PNG} from 'pngjs';
import pixelmatch from 'pixelmatch';
import fs from 'node:fs';
import path from 'node:path';
import {createRequire} from 'node:module';
import {fileURLToPath} from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const require = createRequire(import.meta.url);
const axeSource = fs.readFileSync(require.resolve('axe-core/axe.min.js'), 'utf8');

const args = Object.fromEntries(process.argv.slice(2).map((a) => {
    const [k, ...v] = a.replace(/^--/, '').split('=');
    return [k, v.length ? v.join('=') : true];
}));
const base = (args.base || 'http://localhost:8080').replace(/\/$/, '');
const mode = args.mode || 'light';
const widths = (args.widths || '360,768,1280').split(',').map(Number);
const only = args.only ? args.only.split(',') : null;
const pagesFile = args.pages || path.join(here, 'pages.json');
const outDir = path.join(here, 'results', mode);
const baselineDir = path.join(here, 'baseline', mode);
// Pages compared against a stored baseline: the ones whose look the theme owns. Core pages change with Moodle releases.
const visualPages = (args.visual || 'login,dashboard,mycourses,course').split(',');
const passwords = {
    ana: process.env.AUDIT_PASSWORD_ANA || 'Demo1234!',
    prof: process.env.AUDIT_PASSWORD_PROF || 'Demo1234!',
    admin: process.env.AUDIT_PASSWORD_ADMIN || null,
};
const heights = {360: 800, 768: 1024, 1280: 800};
// Areas where the theme promises 44 x 44 px targets. Elsewhere WCAG 2.5.8 (24 x 24 px or enough spacing) applies.
const strictZones = ['.ct-navbar', '#theme_boost-drawers-primary', '.ct-login-sheet', '.ct-dashboard-hero',
    '.secondary-navigation'];

const pages = JSON.parse(fs.readFileSync(pagesFile, 'utf8')).filter((p) => !only || only.includes(p.id));
fs.mkdirSync(outDir, {recursive: true});

/**
 * Runs in the page: horizontal overflow and its likely culprits.
 *
 * @returns {object}
 */
function measureOverflow() {
    const doc = document.documentElement;
    const overflow = doc.scrollWidth - doc.clientWidth;
    const culprits = [];
    if (overflow > 0) {
        const clipped = (el) => {
            for (let p = el.parentElement; p && p !== document.body; p = p.parentElement) {
                const ox = getComputedStyle(p).overflowX;
                if (ox !== 'visible') {
                    return true;
                }
            }
            return false;
        };
        for (const el of document.body.querySelectorAll('*')) {
            const r = el.getBoundingClientRect();
            if (r.width && r.right > doc.clientWidth + 1 && !clipped(el)) {
                culprits.push(el.tagName.toLowerCase() + (el.id ? '#' + el.id : '') +
                    (el.classList.length ? '.' + [...el.classList].slice(0, 3).join('.') : '') +
                    ` (right ${Math.round(r.right)}px)`);
                if (culprits.length >= 5) {
                    break;
                }
            }
        }
    }
    return {overflow, culprits};
}

/**
 * Runs in the page: interactive elements smaller than the target size.
 *
 * @param {string[]} zones Selectors of the 44 px zones.
 * @returns {object[]}
 */
function measureTargets(zones) {
    const selector = 'a[href], button, input:not([type=hidden]), select, textarea, summary, [role=button], ' +
        '[role=link], [role=tab], [role=menuitem], [tabindex]:not([tabindex="-1"])';
    const visible = (el) => {
        const s = getComputedStyle(el);
        const r = el.getBoundingClientRect();
        return s.visibility !== 'hidden' && s.display !== 'none' && r.width > 0 && r.height > 0 &&
            r.bottom > 0 && r.top < window.innerHeight * 3 && !el.closest('[aria-hidden=true], .visually-hidden, .sr-only');
    };
    // WCAG 2.5.8 exception: a link inside a sentence or block of text.
    const inline = (el) => {
        if (el.tagName !== 'A') {
            return false;
        }
        const display = getComputedStyle(el).display;
        const parent = el.parentElement;
        return display.startsWith('inline') && parent &&
            [...parent.childNodes].some((n) => n.nodeType === 3 && n.textContent.trim().length > 1);
    };
    // A custom control drawn on top of a hidden native input: measure the label, which is the real target.
    const targetRect = (el) => {
        // A link stretched over its card with an absolutely positioned ::after: the card is the target.
        const after = getComputedStyle(el, '::after');
        if (after.content !== 'none' && after.position === 'absolute' && after.top === '0px' && after.left === '0px') {
            for (let p = el.parentElement; p; p = p.parentElement) {
                if (getComputedStyle(p).position !== 'static') {
                    return p.getBoundingClientRect();
                }
            }
        }
        if (el.matches('input[type=checkbox], input[type=radio]') && el.labels && el.labels[0]) {
            const a = el.getBoundingClientRect();
            const b = el.labels[0].getBoundingClientRect();
            return {left: Math.min(a.left, b.left), top: Math.min(a.top, b.top), right: Math.max(a.right, b.right),
                bottom: Math.max(a.bottom, b.bottom)};
        }
        return el.getBoundingClientRect();
    };
    const all = [...document.querySelectorAll(selector)].filter(visible);
    const rects = all.map(targetRect);
    const describe = (el) => {
        const text = (el.getAttribute('aria-label') || el.textContent || el.value || el.title || '').trim()
            .replace(/\s+/g, ' ').slice(0, 40);
        return el.tagName.toLowerCase() + (el.id ? '#' + el.id : '') +
            (el.classList.length ? '.' + [...el.classList].slice(0, 2).join('.') : '') + (text ? ` "${text}"` : '');
    };
    const problems = [];
    all.forEach((el, i) => {
        const r = rects[i];
        const w = r.right - r.left;
        const h = r.bottom - r.top;
        const zone = zones.find((z) => el.closest(z));
        if (zone) {
            if (w < 43.5 || h < 43.5) {
                problems.push({rule: 'target-44', zone, el: describe(el), size: `${Math.round(w)}x${Math.round(h)}`});
            }
            return;
        }
        if ((w >= 23.5 && h >= 23.5) || inline(el)) {
            return;
        }
        // Spacing exception: a 24 px square centred on the target must not overlap another target.
        const cx = (r.left + r.right) / 2;
        const cy = (r.top + r.bottom) / 2;
        const clash = rects.some((o, j) => j !== i && !all[j].contains(el) && !el.contains(all[j]) &&
            o.left < cx + 12 && o.right > cx - 12 && o.top < cy + 12 && o.bottom > cy - 12);
        if (clash) {
            problems.push({rule: 'target-24', el: describe(el), size: `${Math.round(w)}x${Math.round(h)}`});
        }
    });
    return problems;
}

/**
 * Compare a screenshot with its baseline; returns the share of different pixels, or null without a baseline.
 *
 * @param {string} file
 * @param {string} baseline
 * @param {string} diffFile
 * @returns {number|null}
 */
function compare(file, baseline, diffFile) {
    if (!fs.existsSync(baseline)) {
        return null;
    }
    const a = PNG.sync.read(fs.readFileSync(file));
    const b = PNG.sync.read(fs.readFileSync(baseline));
    if (a.width !== b.width || a.height !== b.height) {
        return 1;
    }
    const diff = new PNG({width: a.width, height: a.height});
    const changed = pixelmatch(a.data, b.data, diff.data, a.width, a.height, {threshold: 0.1});
    const ratio = changed / (a.width * a.height);
    if (ratio > 0.001) {
        fs.writeFileSync(diffFile, PNG.sync.write(diff));
    }
    return ratio;
}

const reportName = args.report || 'report';

// Right after a cache purge Moodle serves Boost's precompiled CSS while it compiles the theme: measuring then would
// audit the wrong stylesheet. Wait until the served stylesheet is the theme's own.
async function waitForThemeCss() {
    const api = await request.newContext();
    try {
        for (let i = 0; i < 60; i++) {
            try {
                const html = await (await api.get(base + '/login/index.php')).text();
                const href = (html.match(/href="([^"]*\/theme\/styles\.php\/[^"]+)"/) || [])[1];
                if (href && (await (await api.get(href.replace(/&amp;/g, '&'))).text()).includes('.ct-login-grid')) {
                    return;
                }
            } catch (e) {
                // The built-in PHP server drops connections under load: try again.
            }
            await new Promise((r) => setTimeout(r, 5000));
        }
        throw new Error('The theme stylesheet was not served after 5 minutes');
    } finally {
        await api.dispose();
    }
}
await waitForThemeCss();

const browser = await chromium.launch();
const sessions = {};

/**
 * Log a user in once and keep the cookies for every width.
 *
 * @param {string} user
 * @returns {Promise<object|null>}
 */
async function storageFor(user) {
    if (user === 'anon') {
        return undefined;
    }
    if (sessions[user] !== undefined) {
        return sessions[user];
    }
    if (!passwords[user]) {
        console.warn(`No password for ${user}; skipping their pages (set AUDIT_PASSWORD_${user.toUpperCase()}).`);
        sessions[user] = null;
        return null;
    }
    const context = await browser.newContext();
    const page = await context.newPage();
    await page.goto(base + '/login/index.php');
    await page.fill('#username', user);
    await page.fill('#password', passwords[user]);
    await Promise.all([page.waitForNavigation(), page.click('#loginbtn')]);
    if (page.url().includes('/login/')) {
        throw new Error(`Could not log in as ${user}`);
    }
    sessions[user] = await context.storageState();
    await context.close();
    return sessions[user];
}

/**
 * Turn editing on or off with the edit mode switch in the header.
 *
 * @param {import('playwright').Page} page
 * @param {boolean} on
 */
async function setEditing(page, on) {
    const toggle = page.locator('.editmode-switch-form input[type=checkbox]').first();
    if (!(await toggle.count()) || (await toggle.isChecked()) === on) {
        return;
    }
    await toggle.evaluate((el) => el.click());
    // The switch saves the preference and reloads the page asynchronously: wait for the result, not for the click.
    await page.waitForFunction((value) => document.body.classList.contains('editing') === value, on, {timeout: 30000});
    await page.waitForLoadState('networkidle');
}

const report = [];
for (const width of widths) {
    for (const p of pages) {
        const storage = await storageFor(p.user);
        if (storage === null) {
            continue;
        }
        const context = await browser.newContext({
            viewport: {width, height: heights[width] || 900},
            reducedMotion: 'reduce',
            storageState: storage,
        });
        const page = await context.newPage();
        const errors = [];
        page.on('pageerror', (e) => errors.push('pageerror: ' + e.message));
        page.on('console', (m) => {
            if (m.type() === 'error' && !/favicon|Failed to load resource: the server responded with a status of 404/
                .test(m.text())) {
                errors.push('console: ' + m.text().slice(0, 200));
            }
        });
        const entry = {id: p.id, width, mode, url: p.url, user: p.user};
        let actions = [];
        try {
            await page.goto(base + p.url, {waitUntil: 'networkidle', timeout: 60000});
            // Actions, comma separated: "edit" turns editing on, "click:<selector>" clicks the first match (a button
            // that opens a modal, or submits a form) and waits for the page to settle.
            actions = (p.action || '').split(',').filter(Boolean);
            for (const action of actions) {
                if (action === 'edit') {
                    await setEditing(page, true);
                } else if (action.startsWith('click:')) {
                    await page.locator(action.slice(6)).first().click();
                    await page.waitForLoadState('networkidle');
                    await page.waitForTimeout(800);
                }
            }
            await page.waitForTimeout(300);
            entry.title = await page.title();
            entry.theme = await page.evaluate(() => document.documentElement.getAttribute('data-bs-theme'));
            entry.overflow = await page.evaluate(measureOverflow);
            entry.targets = await page.evaluate(measureTargets, strictZones);
            await page.addScriptTag({content: axeSource});
            const axe = await page.evaluate(async() => {
                // eslint-disable-next-line no-undef
                const r = await axe.run(document, {
                    runOnly: {type: 'tag', values: ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa']},
                    resultTypes: ['violations'],
                });
                return r.violations.map((v) => ({
                    id: v.id, impact: v.impact, help: v.help,
                    nodes: v.nodes.slice(0, 5).map((n) => ({target: n.target.join(' '), html: n.html.slice(0, 160),
                        summary: (n.failureSummary || '').split('\n').slice(1, 2).join(' ').trim().slice(0, 160)})),
                    count: v.nodes.length,
                }));
            });
            entry.axe = axe;
            const shot = path.join(outDir, `${p.id}-${width}.png`);
            await page.screenshot({path: shot});
            if (visualPages.includes(p.id)) {
                const ref = path.join(baselineDir, `${p.id}-${width}.png`);
                if (args['update-baseline']) {
                    fs.mkdirSync(baselineDir, {recursive: true});
                    fs.copyFileSync(shot, ref);
                } else {
                    entry.visualdiff = compare(shot, ref, path.join(outDir, `${p.id}-${width}.diff.png`));
                }
            }
            if (actions.includes('edit')) {
                await page.goto(base + p.url, {waitUntil: 'networkidle', timeout: 60000});
                await setEditing(page, false);
            }
        } catch (e) {
            entry.failure = e.message.split('\n')[0];
        }
        entry.errors = errors;
        report.push(entry);
        const serious = (entry.axe || []).filter((v) => ['serious', 'critical'].includes(v.impact)).length;
        console.log(`${mode} ${width} ${p.id}: overflow ${entry.overflow?.overflow ?? '?'}px, ` +
            `targets ${(entry.targets || []).length}, axe ${(entry.axe || []).length} (${serious} serious+), ` +
            `errors ${errors.length}${entry.failure ? ' FAILED ' + entry.failure : ''}`);
        await context.close();
    }
}
await browser.close();

fs.writeFileSync(path.join(outDir, reportName + '.json'), JSON.stringify(report, null, 2));

// Markdown summary.
const lines = [`# Audit · ${mode} · ${new Date().toISOString().slice(0, 16).replace('T', ' ')}`, '',
    '| Page | Width | Overflow | Targets 44 | Targets 24 | axe serious+ | axe other | JS errors | Visual diff |',
    '|---|---|---|---|---|---|---|---|---|'];
for (const e of report) {
    const t44 = (e.targets || []).filter((t) => t.rule === 'target-44').length;
    const t24 = (e.targets || []).filter((t) => t.rule === 'target-24').length;
    const serious = (e.axe || []).filter((v) => ['serious', 'critical'].includes(v.impact));
    const other = (e.axe || []).length - serious.length;
    const vd = e.visualdiff === undefined ? '' : e.visualdiff === null ? 'no baseline' :
        (e.visualdiff * 100).toFixed(2) + ' %';
    lines.push(`| ${e.id} | ${e.width} | ${e.failure ? 'FAILED' : (e.overflow?.overflow || 0) + ' px'} | ${t44} | ` +
        `${t24} | ${serious.map((v) => v.id).join(', ') || 0} | ${other} | ${e.errors.length} | ${vd} |`);
}
lines.push('', '## Details', '');
for (const e of report) {
    const notes = [];
    if (e.failure) {
        notes.push(`- Failed: ${e.failure}`);
    }
    if (e.overflow?.overflow > 0) {
        notes.push(`- Overflow ${e.overflow.overflow}px: ${e.overflow.culprits.join('; ')}`);
    }
    for (const t of e.targets || []) {
        notes.push(`- ${t.rule} ${t.size} ${t.el}${t.zone ? ' in ' + t.zone : ''}`);
    }
    for (const v of e.axe || []) {
        notes.push(`- axe ${v.impact} ${v.id} (${v.count}): ${v.help}`);
        for (const n of v.nodes) {
            notes.push(`  - \`${n.target}\` ${n.summary}`);
        }
    }
    for (const err of e.errors) {
        notes.push(`- ${err}`);
    }
    if (notes.length) {
        lines.push(`### ${e.id} · ${e.width}px`, ...notes, '');
    }
}
fs.writeFileSync(path.join(outDir, reportName + '.md'), lines.join('\n') + '\n');
console.log(`Report: ${path.join(outDir, reportName + '.md')}`);
