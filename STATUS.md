# STATUS · theme_customboost

## Current milestone
**F0 · Close review findings** (plan phase F0, version 0.1.1): done. GitHub Actions is green on PHP 8.3 and 8.4
(PostgreSQL 17): phpcs, PHPDoc, validation, Mustache lint, Grunt, 29 PHPUnit tests and 13 Behat scenarios.

## F0 · Done
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

## Previous milestone
**M3 · Tuning plan and administrator documentation**: done. Version 0.1.0 (ALPHA) is installable and complete for
the requested scope; the road to 1.0.0 is in `docs/PLAN-PUESTA-A-PUNTO.md`.

## M3 · Done
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
- Phase F1 of the plan: full core page walk-through in both presets, visual regression, automated touch-target
  and overflow checks, axe in CI.

## Later ideas
- Hero image per course category on the personal area cards.
- Course page header with cover image when format_customtiles is not in use.
- Behat scenarios for the login layout and the dashboard cards.
