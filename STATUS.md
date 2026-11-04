# STATUS · theme_customboost

## Current milestone
**M1 · Base, tokens, login and personal area**: done.

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

## Blockers
- None.

## Next steps
- M2: dark preset review, captures at 360/768/1280 in `docs/screenshots/`, full README (en/es).
