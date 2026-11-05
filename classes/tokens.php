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

namespace theme_customboost;

/**
 * Design tokens for theme_customboost.
 *
 * This class is the single source of truth for the colour palette. It builds the light and the dark palette from the
 * brand and accent colours chosen by the administrator, adjusting them when necessary so that every text/background
 * pair reaches WCAG 2.1 AA, and emits them as CSS custom properties in the shared "--ct-" namespace that
 * format_customtiles also reads.
 *
 * Non colour tokens (type scale, radii, shadows, spacing, motion) are static and live in scss/customboost/_tokens.scss.
 *
 * @package    theme_customboost
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tokens {
    /** @var string Namespace prefix shared with format_customtiles. */
    public const PREFIX = '--ct-';

    /** @var string Default brand colour (indigo). */
    public const DEFAULT_BRAND = '#4338ca';

    /** @var string Default accent colour (warm orange). */
    public const DEFAULT_ACCENT = '#c2410c';

    /** @var float Minimum contrast for text. 4.6 rather than 4.5 so rounding in other checkers never reports a fail. */
    public const MIN_TEXT = 4.6;

    /** @var float Minimum contrast for non-text UI (borders of controls, focus ring). */
    public const MIN_UI = 3.0;

    /** @var array Fixed neutral and status colours for the light preset. */
    protected const LIGHT = [
        'bg' => '#f5f6fa',
        'surface' => '#ffffff',
        'surface-2' => '#eef0f6',
        'surface-sunken' => '#e7e9f1',
        'text' => '#151827',
        'text-muted' => '#4f566b',
        'border' => '#dde0ea',
        'border-strong' => '#7c8297',
        'success' => '#13733a',
        'warning' => '#8a5300',
        'danger' => '#c42b2b',
        'info' => '#0b6a94',
        'header-bg' => '#ffffff',
    ];

    /** @var array Fixed neutral and status colours for the dark preset. */
    protected const DARK = [
        'bg' => '#0e1120',
        'surface' => '#161a2d',
        'surface-2' => '#1e233a',
        'surface-sunken' => '#0a0d19',
        'text' => '#e9ebf5',
        'text-muted' => '#a8aec6',
        'border' => '#2b3150',
        'border-strong' => '#6d7494',
        'success' => '#5ad08a',
        'warning' => '#f2c14e',
        'danger' => '#ff8b85',
        'info' => '#6cc6f0',
        'header-bg' => '#12162a',
    ];

    /**
     * Build the light and dark palettes.
     *
     * @param string|null $brand Brand colour as hex, null for the default.
     * @param string|null $accent Accent colour as hex, null for the default.
     * @return array ['light' => [name => hex], 'dark' => [name => hex], 'adjusted' => [notes]]
     */
    public static function build(?string $brand = null, ?string $accent = null): array {
        $brand = self::normalise_hex($brand) ?? self::DEFAULT_BRAND;
        $accent = self::normalise_hex($accent) ?? self::DEFAULT_ACCENT;
        $adjusted = [];

        $light = self::LIGHT;
        $dark = self::DARK;

        // Light mode: the brand colour must be readable as text/link on every light surface where it may appear
        // (the darkest being surface-2), and a fill which carries white text.
        $lightprimary = self::ensure_contrast(
            $brand,
            [$light['surface-2'], $light['bg'], $light['surface']],
            self::MIN_TEXT,
            '#000000'
        );
        if ($lightprimary !== $brand) {
            $adjusted[] = 'primary-light';
        }
        $light['primary'] = $lightprimary;
        $light['primary-hover'] = self::mix('#000000', $lightprimary, 0.15);
        $light['on-primary'] = self::best_on($lightprimary);
        $light['primary-subtle'] = self::mix($lightprimary, $light['surface'], 0.10);
        $light['on-primary-subtle'] = self::ensure_contrast(
            $lightprimary,
            [$light['primary-subtle']],
            self::MIN_TEXT,
            '#000000'
        );

        $lightaccent = self::ensure_contrast(
            $accent,
            [$light['surface-2'], $light['bg'], $light['surface']],
            self::MIN_TEXT,
            '#000000'
        );
        if ($lightaccent !== $accent) {
            $adjusted[] = 'accent-light';
        }
        $light['accent'] = $lightaccent;
        $light['on-accent'] = self::best_on($lightaccent);
        $light['link'] = $lightprimary;
        $light['focus'] = self::ensure_contrast($lightprimary, [$light['bg'], $light['surface']], self::MIN_UI, '#000000');

        // Dark mode: lighten the brand until it reads on the lightest dark surface; fills then carry dark text.
        $darkprimary = self::ensure_contrast(
            $brand,
            [$dark['surface-2'], $dark['surface'], $dark['bg']],
            self::MIN_TEXT,
            '#ffffff'
        );
        $adjusted[] = 'primary-dark';
        $dark['primary'] = $darkprimary;
        $dark['primary-hover'] = self::mix('#ffffff', $darkprimary, 0.18);
        $dark['on-primary'] = self::best_on($darkprimary, $dark['bg']);
        $dark['primary-subtle'] = self::mix($darkprimary, $dark['surface'], 0.16);
        $dark['on-primary-subtle'] = self::ensure_contrast(
            $darkprimary,
            [$dark['primary-subtle']],
            self::MIN_TEXT,
            '#ffffff'
        );
        $darkaccent = self::ensure_contrast(
            $accent,
            [$dark['surface-2'], $dark['surface'], $dark['bg']],
            self::MIN_TEXT,
            '#ffffff'
        );
        $dark['accent'] = $darkaccent;
        $dark['on-accent'] = self::best_on($darkaccent, $dark['bg']);
        $dark['link'] = $darkprimary;
        $dark['focus'] = self::ensure_contrast($darkprimary, [$dark['bg'], $dark['surface']], self::MIN_UI, '#ffffff');

        foreach (['light' => &$light, 'dark' => &$dark] as $mode => &$palette) {
            $base = $mode === 'light' ? '#ffffff' : $palette['bg'];
            $towards = $mode === 'light' ? '#000000' : '#ffffff';
            $surfaces = [$palette['bg'], $palette['surface'], $palette['surface-2']];
            foreach (['success', 'warning', 'danger', 'info'] as $status) {
                // Safety net: the fixed status colours already pass, but keep them honest if they are ever edited.
                $palette[$status] = self::ensure_contrast($palette[$status], $surfaces, self::MIN_TEXT, $towards);
                $palette['on-' . $status] = self::best_on($palette[$status], $base);
                $palette[$status . '-subtle'] = self::mix($palette[$status], $palette['surface'], $mode === 'light' ? 0.10 : 0.16);
            }
        }
        unset($palette);

        return ['light' => $light, 'dark' => $dark, 'adjusted' => $adjusted];
    }

    /**
     * The text/background pairs that must reach AA, with their thresholds.
     *
     * @return array List of [foreground, background, minimum].
     */
    public static function contrast_pairs(): array {
        $pairs = [];
        foreach (['bg', 'surface', 'surface-2'] as $surface) {
            foreach (['text', 'text-muted', 'primary', 'link', 'accent', 'success', 'warning', 'danger', 'info'] as $fg) {
                $pairs[] = [$fg, $surface, 4.5];
            }
            $pairs[] = ['border-strong', $surface, 3.0];
            $pairs[] = ['focus', $surface, 3.0];
        }
        $pairs[] = ['text', 'surface-sunken', 4.5];
        $pairs[] = ['text', 'header-bg', 4.5];
        $pairs[] = ['text-muted', 'header-bg', 4.5];
        $pairs[] = ['on-primary', 'primary', 4.5];
        $pairs[] = ['on-primary', 'primary-hover', 4.5];
        $pairs[] = ['on-primary-subtle', 'primary-subtle', 4.5];
        $pairs[] = ['text', 'primary-subtle', 4.5];
        $pairs[] = ['on-accent', 'accent', 4.5];
        foreach (['success', 'warning', 'danger', 'info'] as $status) {
            $pairs[] = ['on-' . $status, $status, 4.5];
            $pairs[] = ['text', $status . '-subtle', 4.5];
        }
        return $pairs;
    }

    /**
     * Check every pair of a palette.
     *
     * @param array $palette A palette from build().
     * @return array List of failures as strings; empty when the palette passes.
     */
    public static function audit(array $palette): array {
        $failures = [];
        foreach (self::contrast_pairs() as [$fg, $bg, $min]) {
            $ratio = self::contrast($palette[$fg], $palette[$bg]);
            if ($ratio < $min) {
                $failures[] = sprintf('%s (%s) on %s (%s): %.2f < %.1f', $fg, $palette[$fg], $bg, $palette[$bg], $ratio, $min);
            }
        }
        return $failures;
    }

    /**
     * Emit the palettes as CSS.
     *
     * The light palette applies to :root and to [data-bs-theme=light]; the dark one to [data-bs-theme=dark], which is
     * what Boost 5.3 writes on the html tag when a dark colour mode is in use (and what this theme writes for the dark
     * preset when Boost's colour modes are off).
     *
     * @param array $palettes Result of build().
     * @return string CSS.
     */
    public static function to_css(array $palettes): string {
        $css = ":root,\n[data-bs-theme=\"light\"] {\n" . self::declarations($palettes['light']) . "    color-scheme: light;\n}\n";
        $css .= "[data-bs-theme=\"dark\"] {\n" . self::declarations($palettes['dark']) . "    color-scheme: dark;\n}\n";
        return $css;
    }

    /**
     * Custom property declarations for one palette.
     *
     * @param array $palette
     * @return string
     */
    protected static function declarations(array $palette): string {
        $out = '';
        foreach ($palette as $name => $hex) {
            $out .= '    ' . self::PREFIX . 'color-' . $name . ': ' . $hex . ";\n";
            if (in_array($name, ['primary', 'accent', 'text', 'surface', 'bg', 'success', 'warning', 'danger', 'info'])) {
                [$r, $g, $b] = self::hex_to_rgb($hex);
                $out .= '    ' . self::PREFIX . 'color-' . $name . "-rgb: $r, $g, $b;\n";
            }
        }
        return $out;
    }

    /**
     * Normalise a user supplied colour to #rrggbb, or null when it is not a valid hex colour.
     *
     * @param string|null $colour
     * @return string|null
     */
    public static function normalise_hex(?string $colour): ?string {
        $colour = strtolower(trim((string) $colour));
        if (preg_match('/^#?([0-9a-f]{3})$/', $colour, $m)) {
            $c = $m[1];
            return '#' . $c[0] . $c[0] . $c[1] . $c[1] . $c[2] . $c[2];
        }
        if (preg_match('/^#?([0-9a-f]{6})$/', $colour, $m)) {
            return '#' . $m[1];
        }
        return null;
    }

    /**
     * Convert hex to RGB.
     *
     * @param string $hex
     * @return int[]
     */
    public static function hex_to_rgb(string $hex): array {
        $hex = ltrim(self::normalise_hex($hex) ?? '#000000', '#');
        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }

    /**
     * Convert RGB to hex.
     *
     * @param array $rgb
     * @return string
     */
    public static function rgb_to_hex(array $rgb): string {
        return sprintf('#%02x%02x%02x', ...array_map(fn($c) => max(0, min(255, (int) round($c))), $rgb));
    }

    /**
     * Mix two colours. $weight is the share of $a (0..1).
     *
     * @param string $a
     * @param string $b
     * @param float $weight
     * @return string
     */
    public static function mix(string $a, string $b, float $weight): string {
        $ca = self::hex_to_rgb($a);
        $cb = self::hex_to_rgb($b);
        $out = [];
        for ($i = 0; $i < 3; $i++) {
            $out[] = $ca[$i] * $weight + $cb[$i] * (1 - $weight);
        }
        return self::rgb_to_hex($out);
    }

    /**
     * WCAG relative luminance.
     *
     * @param string $hex
     * @return float
     */
    public static function luminance(string $hex): float {
        $l = 0;
        foreach (array_map(null, self::hex_to_rgb($hex), [0.2126, 0.7152, 0.0722]) as [$c, $w]) {
            $c = $c / 255;
            $c = $c <= 0.04045 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
            $l += $c * $w;
        }
        return $l;
    }

    /**
     * WCAG contrast ratio between two colours.
     *
     * @param string $a
     * @param string $b
     * @return float
     */
    public static function contrast(string $a, string $b): float {
        $la = self::luminance($a);
        $lb = self::luminance($b);
        return (max($la, $lb) + 0.05) / (min($la, $lb) + 0.05);
    }

    /**
     * RGB to HSL (all components 0..1).
     *
     * @param array $rgb
     * @return float[]
     */
    public static function rgb_to_hsl(array $rgb): array {
        [$r, $g, $b] = array_map(fn($c) => $c / 255, $rgb);
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;
        if ($max == $min) {
            return [0.0, 0.0, $l];
        }
        $d = $max - $min;
        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
        if ($max == $r) {
            $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
        } else if ($max == $g) {
            $h = ($b - $r) / $d + 2;
        } else {
            $h = ($r - $g) / $d + 4;
        }
        return [$h / 6, $s, $l];
    }

    /**
     * HSL (0..1) to RGB (0..255).
     *
     * @param float $h
     * @param float $s
     * @param float $l
     * @return float[]
     */
    public static function hsl_to_rgb(float $h, float $s, float $l): array {
        if ($s == 0.0) {
            return [$l * 255, $l * 255, $l * 255];
        }
        $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;
        $hue = function (float $t) use ($p, $q): float {
            if ($t < 0) {
                $t += 1;
            }
            if ($t > 1) {
                $t -= 1;
            }
            if ($t < 1 / 6) {
                return $p + ($q - $p) * 6 * $t;
            }
            if ($t < 1 / 2) {
                return $q;
            }
            if ($t < 2 / 3) {
                return $p + ($q - $p) * (2 / 3 - $t) * 6;
            }
            return $p;
        };
        return [$hue($h + 1 / 3) * 255, $hue($h) * 255, $hue($h - 1 / 3) * 255];
    }

    /**
     * Move a colour towards $towards (black or white) in 1% lightness steps until it reaches $min against every background.
     *
     * @param string $colour
     * @param array $backgrounds
     * @param float $min
     * @param string $towards
     * @return string
     */
    public static function ensure_contrast(string $colour, array $backgrounds, float $min, string $towards): string {
        // Step the HSL lightness rather than mixing with black or white, so the hue and the saturation of the brand
        // survive the adjustment (mixing with white turns a saturated indigo into lavender grey).
        [$h, $s, $l] = self::rgb_to_hsl(self::hex_to_rgb($colour));
        $direction = self::luminance($towards) > 0.5 ? 1 : -1;
        for ($step = 0; $step <= 100; $step++) {
            $lightness = max(0, min(1, $l + $direction * $step * 0.01));
            $candidate = $step === 0 ? $colour : self::rgb_to_hex(self::hsl_to_rgb($h, $s, $lightness));
            $ok = true;
            foreach ($backgrounds as $bg) {
                if (self::contrast($candidate, $bg) < $min) {
                    $ok = false;
                    break;
                }
            }
            if ($ok) {
                return $candidate;
            }
        }
        return $towards;
    }

    /**
     * Pick the text colour for a fill: white, or a near black (or the given dark colour) — whichever contrasts more.
     *
     * @param string $fill
     * @param string $dark The dark candidate.
     * @return string
     */
    public static function best_on(string $fill, string $dark = '#111322'): string {
        return self::contrast('#ffffff', $fill) >= self::contrast($dark, $fill) ? '#ffffff' : $dark;
    }
}
