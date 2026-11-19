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

use core_external\external_api;
use theme_customboost\external\preview;

/**
 * Tests for the live preview web service.
 *
 * @package    theme_customboost
 * @category   test
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(preview::class)]
final class external_preview_test extends \advanced_testcase {
    /**
     * Call the service and clean the result like the web service layer does.
     *
     * @param array $args
     * @return array
     */
    private function call(array $args): array {
        $result = preview::execute(...$args);
        return external_api::clean_returnvalue(preview::execute_returns(), $result);
    }

    /**
     * The CSS only targets the preview panels and follows the choices.
     */
    public function test_scoped_css(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $result = $this->call(['palette' => 'forest', 'fontfamily' => 'serif', 'radius' => 'extra', 'density' => 'compact']);

        $this->assertStringContainsString('.ct-preview [data-ct-preview-mode="light"]', $result['css']);
        $this->assertStringContainsString('.ct-preview [data-ct-preview-mode="dark"]', $result['css']);
        $this->assertStringNotContainsString(':root', $result['css']);
        $this->assertStringContainsString('--ct-color-primary: ' . options::PALETTES['forest'][0], $result['css']);
        $this->assertStringContainsString('Iowan Old Style', $result['css']);
        $this->assertStringContainsString('--bs-btn-border-radius: 50rem', $result['css']);
        $this->assertStringContainsString('calc(1rem * .85)', $result['css']);
        $this->assertFalse($result['adjusted']);
        $this->assertSame('', $result['notice']);
    }

    /**
     * A colour without enough contrast is adjusted and explained; nothing from the request reaches the CSS unchecked.
     */
    public function test_adjustment_and_injection(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $result = $this->call(['palette' => 'custom', 'brandcolour' => '#ffcc00']);
        $this->assertTrue($result['adjusted']);
        $this->assertNotSame('', $result['notice']);
        $this->assertNotSame('#ffcc00', $result['lightprimary']);

        $result = $this->call(['palette' => 'custom', 'brandcolour' => 'red;} body {display:none',
            'accentcolour' => '</style><script>', 'fontfamily' => 'x', 'radius' => 'x', 'density' => 'x', 'fontsize' => '99']);
        $this->assertStringNotContainsString('display:none', $result['css']);
        $this->assertStringNotContainsString('<', $result['css']);
        $this->assertStringContainsString('--ct-color-primary: ' . tokens::DEFAULT_BRAND, $result['css']);
    }

    /**
     * Only site administrators can use it.
     */
    public function test_capability(): void {
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user());
        $this->expectException(\required_capability_exception::class);
        preview::execute('indigo');
    }
}
