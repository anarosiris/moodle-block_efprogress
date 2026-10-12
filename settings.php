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
 * Site settings for the block_efprogress plugin.
 *
 * @package    block_efprogress
 * @copyright  2026 Ana Rosiris Castro Mendez, eLearning Fácil (https://elearningfacil.com)
 * @author     Ana Rosiris Castro Mendez <info@elearningfacil.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig && $ADMIN->fulltree) {
    // Course custom field that holds the expected duration. It is chosen ONCE
    // here because the name varies per client (duracion, horas...).
    $settings->add(new admin_setting_configtext(
        'block_efprogress/durationfield',
        get_string('setting_durationfield', 'block_efprogress'),
        get_string('setting_durationfield_desc', 'block_efprogress'),
        'duracion',
        PARAM_ALPHANUMEXT
    ));

    // Default cheer texts (each company with its own tone); they can also be
    // customised per course in the block configuration.
    $settings->add(new admin_setting_configtext(
        'block_efprogress/cheer60',
        get_string('setting_cheer60', 'block_efprogress'),
        get_string('setting_cheer60_desc', 'block_efprogress'),
        '',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'block_efprogress/cheer100',
        get_string('setting_cheer100', 'block_efprogress'),
        get_string('setting_cheer100_desc', 'block_efprogress'),
        '',
        PARAM_TEXT
    ));
}
