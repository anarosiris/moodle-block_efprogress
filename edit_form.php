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

/**
 * Instance configuration form for the block_efprogress plugin.
 *
 * @package    block_efprogress
 * @copyright  2026 Ana Rosiris Castro Mendez, eLearning Fácil (https://elearningfacil.com)
 * @author     Ana Rosiris Castro Mendez <info@elearningfacil.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Instance configuration form (what the manager decides per course).
 *
 * @package    block_efprogress
 * @copyright  2026 Ana Rosiris Castro Mendez, eLearning Fácil (https://elearningfacil.com)
 * @author     Ana Rosiris Castro Mendez <info@elearningfacil.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_efprogress_edit_form extends block_edit_form {
    /**
     * Block-specific fields.
     *
     * @param \MoodleQuickForm $mform Form.
     */
    protected function specific_definition($mform) {
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_efprogress'));
        $mform->setType('config_title', PARAM_TEXT);

        // Layout: "Auto" decides based on the block's real width (container
        // query) — vertical in a side column, horizontal at full width
        // (e.g. Boost Union content region).
        $mform->addElement(
            'select',
            'config_orientation',
            get_string('configorientation', 'block_efprogress'),
            [
                'auto'       => get_string('orientation_auto', 'block_efprogress'),
                'vertical'   => get_string('orientation_vertical', 'block_efprogress'),
                'horizontal' => get_string('orientation_horizontal', 'block_efprogress'),
            ]
        );
        $mform->setDefault('config_orientation', 'auto');

        // My progress section.
        $mform->addElement(
            'advcheckbox',
            'config_showprogress',
            get_string('configshowprogress', 'block_efprogress')
        );
        $mform->setDefault('config_showprogress', 1);

        // Informational notice: how many activities with completion the course has.
        $count = $this->completion_count();
        $mform->addElement(
            'static',
            'completioninfo',
            '',
            html_writer::div(
                get_string('completioncount', 'block_efprogress', $count),
                'alert alert-info py-2 mb-0 small'
            )
        );

        // Restricted activities as locked steps (padlock, no link, reason in
        // the tooltip). Truly hidden activities never appear.
        $mform->addElement(
            'advcheckbox',
            'config_showlocked',
            get_string('configshowlocked', 'block_efprogress')
        );
        $mform->setDefault('config_showlocked', 1);
        $mform->addElement(
            'static',
            'showlockedinfo',
            '',
            html_writer::span(
                get_string('showlocked_hint', 'block_efprogress'),
                'text-muted small'
            )
        );
        $mform->hideIf('config_showlocked', 'config_showprogress', 'notchecked');
        $mform->hideIf('showlockedinfo', 'config_showprogress', 'notchecked');

        // Section: cheer messages.
        $mform->addElement(
            'advcheckbox',
            'config_showcheer',
            get_string('configshowcheer', 'block_efprogress')
        );
        $mform->setDefault('config_showcheer', 1);

        $cheer60default = trim((string) get_config('block_efprogress', 'cheer60'));
        if ($cheer60default === '') {
            $cheer60default = get_string('cheer60_default', 'block_efprogress');
        }
        $mform->addElement(
            'text',
            'config_cheer60',
            get_string('configcheer60', 'block_efprogress'),
            ['size' => 48]
        );
        $mform->setType('config_cheer60', PARAM_TEXT);
        $mform->addElement(
            'static',
            'cheer60info',
            '',
            html_writer::span(
                get_string('cheerdefault', 'block_efprogress', s($cheer60default)),
                'text-muted small'
            )
        );
        $mform->hideIf('config_cheer60', 'config_showcheer', 'notchecked');
        $mform->hideIf('cheer60info', 'config_showcheer', 'notchecked');

        $cheer100default = trim((string) get_config('block_efprogress', 'cheer100'));
        if ($cheer100default === '') {
            $cheer100default = get_string('cheer100_default', 'block_efprogress');
        }
        $mform->addElement(
            'text',
            'config_cheer100',
            get_string('configcheer100', 'block_efprogress'),
            ['size' => 48]
        );
        $mform->setType('config_cheer100', PARAM_TEXT);
        $mform->addElement(
            'static',
            'cheer100info',
            '',
            html_writer::span(
                get_string('cheerdefault', 'block_efprogress', s($cheer100default)),
                'text-muted small'
            )
        );
        $mform->hideIf('config_cheer100', 'config_showcheer', 'notchecked');
        $mform->hideIf('cheer100info', 'config_showcheer', 'notchecked');

        // My time section.
        $mform->addElement(
            'advcheckbox',
            'config_showtime',
            get_string('configshowtime', 'block_efprogress')
        );
        $mform->setDefault('config_showtime', 1);
        if (!\block_efprogress\local\progress::time_available((int) $this->page->course->id)) {
            $mform->addElement(
                'static',
                'timeinfo',
                '',
                html_writer::div(
                    get_string('notime_hint', 'block_efprogress'),
                    'alert alert-secondary py-2 mb-0 small'
                )
            );
        }

        $mform->addElement(
            'select',
            'config_durationsource',
            get_string('configdurationsource', 'block_efprogress'),
            [
                'field'  => get_string('durationsource_field', 'block_efprogress'),
                'manual' => get_string('durationsource_manual', 'block_efprogress'),
                'none'   => get_string('durationsource_none', 'block_efprogress'),
            ]
        );
        $mform->setDefault('config_durationsource', 'field');
        $mform->hideIf('config_durationsource', 'config_showtime', 'notchecked');

        $group = [];
        $group[] = $mform->createElement('text', 'config_durationhours', '', ['size' => 4]);
        $group[] = $mform->createElement(
            'static',
            'hlabel',
            '',
            get_string('hours') . ' '
        );
        $group[] = $mform->createElement('text', 'config_durationmins', '', ['size' => 4]);
        $group[] = $mform->createElement('static', 'mlabel', '', get_string('minutes'));
        $mform->addGroup(
            $group,
            'durationgroup',
            get_string('configdurationmanual', 'block_efprogress'),
            ' ',
            false
        );
        $mform->setType('config_durationhours', PARAM_INT);
        $mform->setType('config_durationmins', PARAM_INT);
        $mform->setDefault('config_durationhours', 0);
        $mform->setDefault('config_durationmins', 0);
        $mform->hideIf('durationgroup', 'config_durationsource', 'neq', 'manual');
        $mform->hideIf('durationgroup', 'config_showtime', 'notchecked');
    }

    /**
     * The manual duration must be whole, non-negative hours and minutes under 60.
     *
     * @param array $data Submitted data.
     * @param array $files Files.
     * @return array Errors.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (($data['config_durationsource'] ?? '') === 'manual' && !empty($data['config_showtime'])) {
            $hours = $data['config_durationhours'] ?? 0;
            $mins = $data['config_durationmins'] ?? 0;
            if (
                !is_numeric($hours) || (int) $hours < 0 || (int) $hours != $hours
                    || !is_numeric($mins) || (int) $mins < 0 || (int) $mins > 59 || (int) $mins != $mins
            ) {
                $errors['durationgroup'] = get_string('configdurationmanual_error', 'block_efprogress');
            }
        }
        return $errors;
    }

    /**
     * Number of course activities with completion configured (visible to the
     * manager, not filtered by user).
     *
     * @return int
     */
    protected function completion_count(): int {
        $course = $this->page->course;
        $completion = new \completion_info($course);
        if (!$completion->is_enabled()) {
            return 0;
        }
        $count = 0;
        foreach (get_fast_modinfo($course)->get_cms() as $cm) {
            if ($cm->deletioninprogress) {
                continue;
            }
            if ((int) $completion->is_enabled($cm) !== COMPLETION_TRACKING_NONE) {
                $count++;
            }
        }
        return $count;
    }
}
