# Design tokens · `--ct-*`

`theme_customboost` publishes its design tokens as CSS custom properties under the **`--ct-`** namespace, the same
one `format_customtiles` reads. A plugin that reads them always gives a fallback, so it keeps working under any
other theme:

```css
.my-tile {
    background: var(--ct-color-surface, #fff);
    color: var(--ct-color-text, #1d2125);
    border-radius: var(--ct-radius-lg, 1rem);
    box-shadow: var(--ct-shadow-sm, none);
}
```

## Contract

* **Where they are defined.** On `:root` / `[data-bs-theme="light"]` (light preset) and on
  `[data-bs-theme="dark"]` (dark preset). Boost 5.3 writes `data-bs-theme` on `<html>`; when Boost's colour modes
  are off, this theme writes it from its *Colour preset* setting.
* **Scoping.** A plugin may override tokens on its own container (e.g. a course format skin on
  `.format-customtiles`), never on `:root`.
* **Stability.** Names listed here are public. New tokens may be added; renaming or removing one is a breaking change.
* **Colours** are generated in PHP (`classes/tokens.php`) from the brand and accent colours and adjusted until every
  pair below reaches WCAG 2.1 AA. Every `--ct-color-X` that can be used with an alpha also has `--ct-color-X-rgb`
  (`r, g, b`) for `rgba(var(--ct-color-primary-rgb), .2)`.
* **Bootstrap/Moodle bridge.** `--bs-body-bg`, `--bs-body-color`, `--bs-link-color`, `--bs-border-color`,
  `--bs-primary-rgb`, the `--mds-*` text/surface tokens and the button/card/dropdown/form/alert variables point at
  the `--ct-*` tokens, so core components and plugins share one palette.

## Colour tokens

| Token | Use |
|---|---|
| `--ct-color-bg` | Page canvas |
| `--ct-color-surface` | Cards, sheets, drawers, header |
| `--ct-color-surface-2` | Raised/secondary surfaces, hover rows |
| `--ct-color-surface-sunken` | Wells, pressed states |
| `--ct-color-text` / `--ct-color-text-muted` | Body text / secondary text |
| `--ct-color-border` / `--ct-color-border-strong` | Dividers (decorative) / control borders (≥ 3:1) |
| `--ct-color-primary`, `-hover`, `on-primary` | Brand fill and the text drawn on it |
| `--ct-color-primary-subtle`, `on-primary-subtle` | Tinted selection (active nav, chips) |
| `--ct-color-accent`, `on-accent` | Highlights |
| `--ct-color-link`, `--ct-color-focus` | Links; focus ring (≥ 3:1) |
| `--ct-color-{success,warning,danger,info}` | Status text/icons; `on-*` for fills, `*-subtle` for tinted backgrounds |
| `--ct-color-header-bg` | App bar (also used for `<meta name="theme-color">`) |

## Other tokens

| Group | Tokens |
|---|---|
| Typography | `--ct-font-family`, `--ct-font-family-heading`, `--ct-font-size-{xs,sm,md,lg,xl,2xl,3xl,4xl}` (2xl+ are fluid `clamp()`), `--ct-line-height`, `--ct-line-height-tight`, `--ct-font-weight-{regular,medium,semibold,bold}`, `--ct-letter-spacing-tight` |
| Shape | `--ct-radius-{sm,md,lg,xl,pill}` |
| Spacing | `--ct-space-{1..8}` (4 px base × `--ct-density`), `--ct-density`, `--ct-gutter` (16/24/32 px at 360/768/1280) |
| Elevation | `--ct-shadow-{sm,md,lg}` (darker in the dark preset) |
| Motion | `--ct-duration-fast`, `--ct-duration`, `--ct-duration-slow`, `--ct-ease` (all neutralised by `prefers-reduced-motion`) |
| Layout | `--ct-touch-target` (44 px), `--ct-header-height` (56 px), `--ct-content-max`, `--ct-focus-width`, `--ct-focus-offset` |

## Contrast report (default colours)

Generated with `php theme/customboost/cli/contrast.php` (exit code 1 if any pair fails). The same check runs in
PHPUnit (`tests/tokens_test.php`) for the default colours and for hostile brand colours (white, black, yellow,
neon green, mid grey…).

== light ==
| Foreground | Background | Ratio | Min | Result |
|---|---|---|---|---|
| text `#151827` | bg `#f5f6fa` | 16.32 | 4.5 | AA |
| text-muted `#4f566b` | bg `#f5f6fa` | 6.76 | 4.5 | AA |
| primary `#4338ca` | bg `#f5f6fa` | 7.32 | 4.5 | AA |
| link `#4338ca` | bg `#f5f6fa` | 7.32 | 4.5 | AA |
| accent `#bd3f0c` | bg `#f5f6fa` | 5.01 | 4.5 | AA |
| success `#13733a` | bg `#f5f6fa` | 5.49 | 4.5 | AA |
| warning `#8a5300` | bg `#f5f6fa` | 5.86 | 4.5 | AA |
| danger `#c42b2b` | bg `#f5f6fa` | 5.22 | 4.5 | AA |
| info `#0b6a94` | bg `#f5f6fa` | 5.55 | 4.5 | AA |
| border-strong `#7c8297` | bg `#f5f6fa` | 3.54 | 3.0 | AA |
| focus `#4338ca` | bg `#f5f6fa` | 7.32 | 3.0 | AA |
| text `#151827` | surface `#ffffff` | 17.62 | 4.5 | AA |
| text-muted `#4f566b` | surface `#ffffff` | 7.30 | 4.5 | AA |
| primary `#4338ca` | surface `#ffffff` | 7.90 | 4.5 | AA |
| link `#4338ca` | surface `#ffffff` | 7.90 | 4.5 | AA |
| accent `#bd3f0c` | surface `#ffffff` | 5.41 | 4.5 | AA |
| success `#13733a` | surface `#ffffff` | 5.93 | 4.5 | AA |
| warning `#8a5300` | surface `#ffffff` | 6.33 | 4.5 | AA |
| danger `#c42b2b` | surface `#ffffff` | 5.63 | 4.5 | AA |
| info `#0b6a94` | surface `#ffffff` | 5.99 | 4.5 | AA |
| border-strong `#7c8297` | surface `#ffffff` | 3.82 | 3.0 | AA |
| focus `#4338ca` | surface `#ffffff` | 7.90 | 3.0 | AA |
| text `#151827` | surface-2 `#eef0f6` | 15.46 | 4.5 | AA |
| text-muted `#4f566b` | surface-2 `#eef0f6` | 6.41 | 4.5 | AA |
| primary `#4338ca` | surface-2 `#eef0f6` | 6.94 | 4.5 | AA |
| link `#4338ca` | surface-2 `#eef0f6` | 6.94 | 4.5 | AA |
| accent `#bd3f0c` | surface-2 `#eef0f6` | 4.75 | 4.5 | AA |
| success `#13733a` | surface-2 `#eef0f6` | 5.20 | 4.5 | AA |
| warning `#8a5300` | surface-2 `#eef0f6` | 5.55 | 4.5 | AA |
| danger `#c42b2b` | surface-2 `#eef0f6` | 4.94 | 4.5 | AA |
| info `#0b6a94` | surface-2 `#eef0f6` | 5.26 | 4.5 | AA |
| border-strong `#7c8297` | surface-2 `#eef0f6` | 3.35 | 3.0 | AA |
| focus `#4338ca` | surface-2 `#eef0f6` | 6.94 | 3.0 | AA |
| text `#151827` | surface-sunken `#e7e9f1` | 14.54 | 4.5 | AA |
| text `#151827` | header-bg `#ffffff` | 17.62 | 4.5 | AA |
| text-muted `#4f566b` | header-bg `#ffffff` | 7.30 | 4.5 | AA |
| on-primary `#ffffff` | primary `#4338ca` | 7.90 | 4.5 | AA |
| on-primary `#ffffff` | primary-hover `#3930ac` | 9.58 | 4.5 | AA |
| on-primary-subtle `#4338ca` | primary-subtle `#ecebfa` | 6.71 | 4.5 | AA |
| text `#151827` | primary-subtle `#ecebfa` | 14.96 | 4.5 | AA |
| on-accent `#ffffff` | accent `#bd3f0c` | 5.41 | 4.5 | AA |
| on-success `#ffffff` | success `#13733a` | 5.93 | 4.5 | AA |
| text `#151827` | success-subtle `#e7f1eb` | 15.25 | 4.5 | AA |
| on-warning `#ffffff` | warning `#8a5300` | 6.33 | 4.5 | AA |
| text `#151827` | warning-subtle `#f3eee6` | 15.26 | 4.5 | AA |
| on-danger `#ffffff` | danger `#c42b2b` | 5.63 | 4.5 | AA |
| text `#151827` | danger-subtle `#f9eaea` | 15.09 | 4.5 | AA |
| on-info `#ffffff` | info `#0b6a94` | 5.99 | 4.5 | AA |
| text `#151827` | info-subtle `#e7f0f4` | 15.24 | 4.5 | AA |
== dark ==
| Foreground | Background | Ratio | Min | Result |
|---|---|---|---|---|
| text `#e9ebf5` | bg `#0e1120` | 15.78 | 4.5 | AA |
| text-muted `#a8aec6` | bg `#0e1120` | 8.52 | 4.5 | AA |
| primary `#8b85de` | bg `#0e1120` | 5.81 | 4.5 | AA |
| link `#8b85de` | bg `#0e1120` | 5.81 | 4.5 | AA |
| accent `#f15c1f` | bg `#0e1120` | 5.62 | 4.5 | AA |
| success `#5ad08a` | bg `#0e1120` | 9.67 | 4.5 | AA |
| warning `#f2c14e` | bg `#0e1120` | 11.18 | 4.5 | AA |
| danger `#ff8b85` | bg `#0e1120` | 8.29 | 4.5 | AA |
| info `#6cc6f0` | bg `#0e1120` | 9.80 | 4.5 | AA |
| border-strong `#6d7494` | bg `#0e1120` | 4.09 | 3.0 | AA |
| focus `#8b85de` | bg `#0e1120` | 5.81 | 3.0 | AA |
| text `#e9ebf5` | surface `#161a2d` | 14.48 | 4.5 | AA |
| text-muted `#a8aec6` | surface `#161a2d` | 7.82 | 4.5 | AA |
| primary `#8b85de` | surface `#161a2d` | 5.34 | 4.5 | AA |
| link `#8b85de` | surface `#161a2d` | 5.34 | 4.5 | AA |
| accent `#f15c1f` | surface `#161a2d` | 5.16 | 4.5 | AA |
| success `#5ad08a` | surface `#161a2d` | 8.87 | 4.5 | AA |
| warning `#f2c14e` | surface `#161a2d` | 10.26 | 4.5 | AA |
| danger `#ff8b85` | surface `#161a2d` | 7.61 | 4.5 | AA |
| info `#6cc6f0` | surface `#161a2d` | 9.00 | 4.5 | AA |
| border-strong `#6d7494` | surface `#161a2d` | 3.75 | 3.0 | AA |
| focus `#8b85de` | surface `#161a2d` | 5.34 | 3.0 | AA |
| text `#e9ebf5` | surface-2 `#1e233a` | 13.02 | 4.5 | AA |
| text-muted `#a8aec6` | surface-2 `#1e233a` | 7.03 | 4.5 | AA |
| primary `#8b85de` | surface-2 `#1e233a` | 4.80 | 4.5 | AA |
| link `#8b85de` | surface-2 `#1e233a` | 4.80 | 4.5 | AA |
| accent `#f15c1f` | surface-2 `#1e233a` | 4.64 | 4.5 | AA |
| success `#5ad08a` | surface-2 `#1e233a` | 7.98 | 4.5 | AA |
| warning `#f2c14e` | surface-2 `#1e233a` | 9.22 | 4.5 | AA |
| danger `#ff8b85` | surface-2 `#1e233a` | 6.84 | 4.5 | AA |
| info `#6cc6f0` | surface-2 `#1e233a` | 8.09 | 4.5 | AA |
| border-strong `#6d7494` | surface-2 `#1e233a` | 3.37 | 3.0 | AA |
| focus `#8b85de` | surface-2 `#1e233a` | 4.80 | 3.0 | AA |
| text `#e9ebf5` | surface-sunken `#0a0d19` | 16.29 | 4.5 | AA |
| text `#e9ebf5` | header-bg `#12162a` | 15.05 | 4.5 | AA |
| text-muted `#a8aec6` | header-bg `#12162a` | 8.12 | 4.5 | AA |
| on-primary `#0e1120` | primary `#8b85de` | 5.81 | 4.5 | AA |
| on-primary `#0e1120` | primary-hover `#a09be4` | 7.42 | 4.5 | AA |
| on-primary-subtle `#938de0` | primary-subtle `#292b49` | 4.64 | 4.5 | AA |
| text `#e9ebf5` | primary-subtle `#292b49` | 11.50 | 4.5 | AA |
| on-accent `#0e1120` | accent `#f15c1f` | 5.62 | 4.5 | AA |
| on-success `#0e1120` | success `#5ad08a` | 9.67 | 4.5 | AA |
| text `#e9ebf5` | success-subtle `#21373c` | 10.54 | 4.5 | AA |
| on-warning `#0e1120` | warning `#f2c14e` | 11.18 | 4.5 | AA |
| text `#e9ebf5` | warning-subtle `#393532` | 10.22 | 4.5 | AA |
| on-danger `#0e1120` | danger `#ff8b85` | 8.29 | 4.5 | AA |
| text `#e9ebf5` | danger-subtle `#3b2c3b` | 10.98 | 4.5 | AA |
| on-info `#0e1120` | info `#6cc6f0` | 9.80 | 4.5 | AA |
| text `#e9ebf5` | info-subtle `#24364c` | 10.35 | 4.5 | AA |
