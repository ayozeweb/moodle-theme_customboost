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

namespace theme_customboost\local;

use theme_customboost\options;
use theme_customboost\tokens;

/**
 * Upgrade steps that are more than a savepoint, kept here so that they can be tested.
 *
 * @package    theme_customboost
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class upgrade {
    /**
     * 0.5.0 adds ready-made palettes; the colour pickers only apply with the "Custom" palette. A site that had changed
     * its colours gets that palette, so it keeps them.
     *
     * @return bool Whether the custom palette was selected.
     */
    public static function select_custom_palette(): bool {
        if (get_config('theme_customboost', 'palette') !== false) {
            return false;
        }
        foreach (['brandcolour' => tokens::DEFAULT_BRAND, 'accentcolour' => tokens::DEFAULT_ACCENT] as $name => $default) {
            $value = tokens::normalise_hex(get_config('theme_customboost', $name) ?: null);
            if ($value !== null && $value !== $default) {
                set_config('palette', options::PALETTE_CUSTOM, 'theme_customboost');
                return true;
            }
        }
        return false;
    }
}
