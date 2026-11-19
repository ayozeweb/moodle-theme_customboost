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

    /**
     * Waits for the live preview of the theme settings to use a brand colour in the light panel.
     *
     * @Then the theme preview should use :colour as the brand colour in theme_customboost
     * @param string $colour Hex colour, e.g. #147c3b.
     */
    public function the_theme_preview_should_use_brand_colour(string $colour): void {
        $colour = strtolower($colour);
        $this->spin(function () use ($colour): bool {
            $css = (string) $this->evaluate_script(
                'return (document.querySelector(\'[data-region="ct-preview-style"]\') || {}).textContent || "";'
            );
            if (!preg_match('/data-ct-preview-mode="light"\] \{[^}]*--ct-color-primary: (#[0-9a-f]{6});/', $css, $m)) {
                throw new ExpectationException('The preview has no colours yet', $this->getSession());
            }
            if ($m[1] !== $colour) {
                throw new ExpectationException("The preview uses {$m[1]}, not {$colour}", $this->getSession());
            }
            return true;
        });
    }

    /**
     * Check stored plugin settings: rows of name, expected value and plugin.
     *
     * @Then the following theme_customboost settings should be stored:
     * @param \Behat\Gherkin\Node\TableNode $table
     */
    public function the_following_settings_should_be_stored(\Behat\Gherkin\Node\TableNode $table): void {
        foreach ($table->getRows() as [$name, $expected, $plugin]) {
            global $DB;
            // From the database: get_config() in the test runner keeps its own cache of what the site has since changed.
            $actual = $DB->get_field('config_plugins', 'value', ['plugin' => $plugin, 'name' => $name]);
            if ((string) $actual !== $expected) {
                throw new \Behat\Mink\Exception\ExpectationException(
                    "{$plugin}/{$name} is '{$actual}', expected '{$expected}'",
                    $this->getSession()
                );
            }
        }
    }

    /**
     * Core's accessibility check, given the time a heavy page needs.
     *
     * Core polls for axe's results without pausing, so on a heavy page (the theme settings, with every tab and its
     * editors in the DOM) it gives up with "No data" while axe is still running. Starting again would fail too ("Axe is
     * already running"), so this waits for that same run to finish and reads its results.
     *
     * @Then the heavy page should meet accessibility standards in theme_customboost
     */
    public function the_heavy_page_should_meet_accessibility_standards(): void {
        try {
            \behat_context_helper::get('behat_accessibility')->run_axe_validation_for_tags();
            return;
        } catch (\Exception $e) {
            if ($e->getMessage() !== 'No data') {
                throw $e;
            }
        }
        $read = 'return document.querySelector(\'script[data-purpose="axe"]\').dataset.results;';
        $results = null;
        for ($i = 0; $i < 60 && !$results; $i++) {
            usleep(500000);
            $results = json_decode($this->evaluate_script($read) ?? '');
        }
        if (!$results) {
            throw new ExpectationException('Axe did not finish within 30 seconds', $this->getSession());
        }
        if ($results->exception !== null) {
            throw new ExpectationException(json_encode($results->exception), $this->getSession());
        }
        if ($results->violations) {
            $ids = array_map(fn($violation) => $violation->id . ' (' . count($violation->nodes) . ')', $results->violations);
            throw new ExpectationException('Accessibility violations: ' . implode(', ', $ids), $this->getSession());
        }
    }
}
