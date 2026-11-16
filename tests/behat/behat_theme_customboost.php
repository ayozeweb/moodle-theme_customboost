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

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Mink\Exception\ExpectationException;

/**
 * Behat steps for theme_customboost.
 *
 * @package    theme_customboost
 * @category   test
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_theme_customboost extends behat_base {
    /**
     * Checks that the page does not scroll horizontally: the document is not wider than the viewport.
     *
     * @Then the page should not scroll horizontally in theme_customboost
     */
    public function the_page_should_not_scroll_horizontally(): void {
        if (!$this->running_javascript()) {
            throw new \Behat\Mink\Exception\DriverException('Checking horizontal scroll requires JavaScript');
        }
        $overflow = (int) $this->evaluate_script(
            'return document.documentElement.scrollWidth - document.documentElement.clientWidth;'
        );
        if ($overflow > 0) {
            throw new ExpectationException(
                "The page is {$overflow}px wider than the viewport",
                $this->getSession()
            );
        }
    }
}
