# STATUS · theme_customboost

## Current milestone
**F2 · Administration P1** (plan phase F2, version 0.5.0 beta): done.

## F2 · Done
- `classes/options.php`: one source of truth for every choice (palettes, fonts, sizes, radii, densities, login
  layouts, dashboard sources, card elements). Every reader validates and falls back to the default.
- Settings in six tabs (Appearance, Header, Login page, Personal area, Footer, Advanced) instead of the nine first
  proposed: the live preview can only follow the fields of its own tab, so everything visual sits with it. The
  deviation is recorded in the plan (4.2).
- New P1 settings: palettes (ADM-M1), dark logos (M4), typeface (T1), text size 15-18 px (T4; 15 is Moodle's
  base, not 16 as first planned), radius (F1), density (F2), site name with the logo (N1), login layouts (L1), text
  under the login form (L5), login image resize to 2000 px (L6), dashboard source (D1), greeting and subtitle (D3),
  card elements (D4), footer text (P1).
- Live preview (ADM-X1/X2): AMD module + web service `theme_customboost_preview`, which runs the same token code as
  the stylesheet, scoped to the preview panels. Requires `moodle/site:config`.
- Tools page `tools.php` (ADM-A1/A2/A4): contrast report, JSON export, import (each value through the setting's own
  `write_setting()` and read back; forced settings skipped), restore defaults with confirmation.
- Upgrade: a site with its own colours gets the "Custom" palette and keeps them (`local\upgrade`).
- Fixes found while verifying: dark logo URL returned as a string broke the login page; `hide_if` on a tab is ignored
  (moved to the tabs page); long select options overflowed the settings page by 40 px at 360 px; container queries
  replaced by a media query (Moodle's stylelint rejects them).
- Verification ADM-V1 to V9 all ✅ (see the plan, 4.5): PHPUnit 58 tests / 615 assertions; Behat 32 scenarios
  (new `settings.feature`: preview, saving, import/restore, four login layouts, footer and dashboard options);
  audit of the settings and tools pages at 360/768/1280 in both modes; extreme combination sampled on 11 pages.
- Behat: custom step for heavy pages, because core's axe step stops polling before axe finishes on the settings
  page ("No data", intermittent).

## Previous milestones

### F1 · Done
- `tests/audit`: Playwright + axe audit of 34 pages at 360/768/1280 px in both presets (overflow, touch targets,
  WCAG 2.2 AA, JS errors, visual regression against `tests/audit/baseline`). Every run is compared with Boost to
  tell theme findings from core ones. `tests/fixtures/demo_site.php` creates the demo data.
- Theme-caused findings fixed (all of them): course cards shown on the messaging page (same layout), muted text
  at 4.47:1 on the tinted canvas, 40px nav pills and edit switch, small core controls too close together, login-flow
  forms wider than a phone, 1-70px overflows at 360px, words split by `overflow-wrap: anywhere`, invalid ARIA in the
  settings tabs, white text on light fills in dark mode, secondary button and file-type hints in dark mode, the
  yellow `.bg-warning` fill lost to a brown text colour (2.55:1), and links inside alerts in dark mode.
- Dark preset patches for core plugins with literal colours (`_dark-core.scss`): mod_assign, gradereport_user and
  search highlights. They fail with Boost's dark mode too; the theme fixes them because it offers a dark preset.
- Remaining findings are core and identical with Boost: two wide data tables (grader, assignment submissions; exempt
  from WCAG 1.4.10), the bulk delete button at 360px in edit mode, a focusable hidden element on the messaging page,
  and the developer exception page (debug display only).
- Behat: 24 scenarios (8 with axe, contrast included, in both presets), all green locally with headless Chrome and
  in CI. New: administration.feature (wrong password, multilang and long texts, login image upload, invalid SCSS,
  editing mode, Boost colour modes with the user selector).
- PHPUnit: 31 tests / 183 assertions (30 courses with limit and order, card states, warning fill regression).
- Verified by hand: install from ZIP (installer validator), upgrade 0.1.0 → 0.2.0, uninstall and reinstall,
  automatic preset following the system in real time.
- Packaging: `.gitattributes` keeps screenshots and visual baselines out of the ZIP (15 MB → 413 KB).


### F0 · Done
- GitHub Actions was failing on every push: `phpcs` (language string order) and `grunt` (80 stylelint problems in
  the SCSS). Both fixed; stylelint is now run locally with Moodle's `.stylelintrc`. PHPUnit (PHP 8.3 and 8.4,
  PostgreSQL 17), Mustache lint, PHPDoc and validation were already green on GitHub.
- H-01/H-02: one escaping convention. PHP exports plain text (`format_string(..., ['escape' => false])`) and the
  templates escape once with `{{ }}`. Covered by PHPUnit and Behat.
- H-04: 1 MB limit for the login image. H-05: palettes cached per request.
- H-06: `cli/build_css.php` builds `style/moodle.css` keeping the `[[pix]]`/`[[font]]` placeholders. Decision: the
  file is a release artefact, not tracked in Git (1.3 MB that would go stale on every SCSS change).
- H-08: 13 Behat scenarios; 11 run locally and all 13 (including the 2 `@javascript` mobile ones) pass in CI.
- New finding H-09: `editor_scss` pointed to a missing `scss/editor.scss` (PHP warning, unstyled editor). Added.
- Removed three unused strings (`continuecourse`, `continuecoursename`, `skiptocontent`).
- Tests: 29 PHPUnit tests / 169 assertions; version bumped to 0.1.1.
- H-03 (inline script for the automatic preset) accepted and documented: same mechanism as Boost.

### M3 · Tuning plan and administrator documentation

Version 0.1.0 (ALPHA) was installable and complete for the requested scope; the road to 1.0.0 is in
`docs/PLAN-PUESTA-A-PUNTO.md`.
- `docs/GUIA-ADMINISTRACION.md`: full administrator guide in Spanish (install, every setting, recipes,
  troubleshooting, FAQ, settings reference). Documents only what exists in 0.1.0.
- `docs/PLAN-PUESTA-A-PUNTO.md`: verification plan (functional, mobile, administration/customisation, security,
  accessibility, compatibility, performance), roadmap F0-F5 with exit criteria, risks and a report template.
- Code review while writing the plan found 8 items (H-01 to H-08 in the plan). None is exploitable by an
  unprivileged user; H-02 (double escaping of the greeting and the category name) is a visible defect for names
  with `&` or `'`. Fixed in F0 (above).

## Done
- Child theme of Boost for Moodle 5.3 (`requires` 2026100500, ALPHA, 0.1.0) with en/es strings and a null Privacy provider.
- Design tokens in the shared `--ct-` namespace (see `docs/tokens.md`); colours generated in PHP with an AA guarantee.
- Light / dark / automatic presets wired to Boost 5.3 colour modes (`data-bs-theme`).
- Login page with an image (own SVG illustration by default, or an image uploaded by the admin).
- Personal area: greeting and "continue learning" course cards with progress.
- Compact 56 px header with the brand visible on mobile; restyled nav pills, drawers, cards, forms and buttons.
- Inter font served locally (OFL); no CDN.
- PHPUnit: tokens (AA for 10 brand colours), dashboard, hook. CLI contrast report.

## Decisions
- **Namespace `--ct-`**: format_customtiles defines no prefix in its spec; `--ct-` is short and neutral. The contract
  (names, scoping, fallbacks) is in `docs/tokens.md`.
- **Colours in PHP rather than SCSS**: the same code gives the CSS, the admin "colours in use" message, the
  `theme-color` meta tag and the contrast tests, so the AA guarantee is tested, not just hoped for.
- **Adjust by HSL lightness** (not by mixing with black/white): keeps the brand's hue and saturation.
- **Presets on top of Boost's colour modes**: when Boost's experimental colour modes are on, users choose and Boost
  decides; when off, the theme's preset writes `data-bs-theme`. Boost's own dark palette is fed from our tokens
  (`$dark-surface-*`), so core components and our components switch together.
- **Dashboard cards via `core_renderer::full_header()`** on `mydashboard` instead of copying Boost's `drawers`
  template: less to maintain on upgrades.
- **Only the navbar template is overridden** (brand visible on mobile); everything else is CSS on core markup.
- **Login keeps core's `core/loginform`** (moved to core in 5.3) and only replaces the layout.

## Local environment notes
- Moodle 5.3+ (MOODLE_503_STABLE) at `/home/user/moodle`, PHP 8.3, built-in PHP server.
- PostgreSQL 17 was not installable in the sandbox (repositories blocked); PostgreSQL 16 was used and the 5.3
  `environment.xml` check was relaxed **in the local checkout only**. This does not affect the plugin.
- core_scss rejects imports through symlinks, so the theme is copied into the checkout (`sync.sh`) instead.

## M2 · Done
- Dark preset reviewed on every page; `.btn-subtle-*` (activity completion) and standalone links (card titles,
  section and activity names) aligned with the tokens. Prose links keep Boost's dark-mode underline (WCAG 1.4.1).
- "All my courses" is only shown when *My courses* is enabled (`enablemycourses`, off by default in 5.3; without it
  the course list is the overview block on the same page).
- 27 real captures in `docs/screenshots/` (login, personal area, My courses, course, mobile navigation; light and
  dark; 360/768/1280 px) taken with Playwright against the local Moodle 5.3. No page scrolls horizontally at 360 px
  (checked by the capture script).
- PHPUnit: 19 tests, 51 assertions, green, no deprecations (PHPUnit 11 attributes).
- phpcs (moodle-cs v3.7.0, `moodle` standard): 0 errors, 0 warnings.
- `cli/contrast.php`: all 98 pairs pass AA for the default colours (and for `#ffcc00`/`#39ff14`).
- Full README in Spanish and English with gallery.

## Not verified in the sandbox
- `moodle-plugin-ci mustache` and `grunt` (need Java/Node toolchains from blocked hosts); the workflow in
  `.github/workflows/ci.yml` runs them on GitHub. Templates were rendered on every captured page without errors.
- Spanish captures: the Spanish core language pack could not be downloaded (download.moodle.org blocked), so the
  captures are in English. The theme's own strings are complete in `lang/es`.
- PostgreSQL 17 (see environment notes); tests ran on PostgreSQL 16.

## Blockers
- None.

## Next steps
- Phase F3 of the plan: mobile on real devices and accessibility (MOV-03 to 16, ACC-10 to 37), including C-02
  (bulk edit bar at 360 px, also seen at 18 px text size with another button).
- F2 leftovers, not blocking: link from each setting to the published guide (ADM-X3).
- F1 leftovers, not blocking: OAuth 2 / MFA login screens, PNG/WebP/AVIF and portrait login images, block drag and
  drop, the colour mode cookie on the login page, overflow at 320/344/375/412 px.

## Later ideas
- Hero image per course category on the personal area cards.
- Course page header with cover image when format_customtiles is not in use.
