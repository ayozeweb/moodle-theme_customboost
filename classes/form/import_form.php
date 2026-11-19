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

namespace theme_customboost\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form to import the theme settings from an export file or pasted text.
 *
 * @package    theme_customboost
 * @copyright  2026 Ayoze de Paz
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class import_form extends \moodleform {
    /**
     * Form definition.
     */
    protected function definition() {
        $mform = $this->_form;
        $mform->addElement('filepicker', 'importfile', get_string('importfile', 'theme_customboost'), null, [
            'accepted_types' => ['.json'],
            'maxbytes' => \theme_customboost\local\settings_io::MAX_SIZE,
        ]);
        $mform->addElement('textarea', 'importtext', get_string('importtext', 'theme_customboost'), ['rows' => 6, 'cols' => 60]);
        $mform->setType('importtext', PARAM_RAW);
        $mform->addElement('hidden', 'action', 'import');
        $mform->setType('action', PARAM_ALPHA);
        $this->add_action_buttons(false, get_string('importapply', 'theme_customboost'));
    }

    /**
     * One of the two sources is required.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (trim($data['importtext'] ?? '') === '' && !$this->get_draft_files('importfile')) {
            $errors['importtext'] = get_string('importnothing', 'theme_customboost');
        }
        return $errors;
    }

    /**
     * The JSON to import: the uploaded file if there is one, else the pasted text.
     *
     * @return string
     */
    public function get_json(): string {
        $content = $this->get_file_content('importfile');
        if ($content !== false && $content !== '') {
            return $content;
        }
        return trim((string) ($this->get_data()->importtext ?? ''));
    }
}
