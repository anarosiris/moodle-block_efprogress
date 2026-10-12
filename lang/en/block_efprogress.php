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
 * English strings for the block_efprogress plugin.
 *
 * @package    block_efprogress
 * @copyright  2026 Ana Rosiris Castro Mendez, eLearning Fácil (https://elearningfacil.com)
 * @author     Ana Rosiris Castro Mendez <info@elearningfacil.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['checkfiles'] = 'Plugin file integrity';
$string['checkfiles_badmanifest'] = 'The integrity manifest could not be read.';
$string['checkfiles_corrupt'] = 'empty or corrupted';
$string['checkfiles_error'] = '{$a} plugin file(s) missing, empty or corrupted. The installation is incomplete.';
$string['checkfiles_fix'] = 'Reinstall the plugin from its zip. If it happens again, extract the zip with the hosting file manager instead of the web installer, then purge the caches.';
$string['checkfiles_missing'] = 'missing';
$string['checkfiles_nomanifest'] = 'No integrity manifest found (development install): nothing to verify.';
$string['checkfiles_ok'] = 'All {$a} plugin files verified: present and intact.';
$string['cheer100_default'] = '🎉 Activities completed!';
$string['cheer60_default'] = 'Keep going, you are almost there!';
$string['cheerdefault'] = 'Leave empty to use the site default: "{$a}"';
$string['clicktogo'] = 'click to open';
$string['completioncount'] = 'This course has {$a} activity(ies) with completion configured. Only those appear in the block; the list updates itself when activities are added or removed.';
$string['configcheer100'] = 'Message at 100%';
$string['configcheer60'] = 'Message from 60%';
$string['configdurationmanual'] = 'Expected duration (manual)';
$string['configdurationmanual_error'] = 'Whole hours (0 or more) and minutes from 0 to 59.';
$string['configdurationsource'] = 'Source of the expected course duration';
$string['configorientation'] = 'Layout';
$string['configshowcheer'] = 'Show encouragement messages';
$string['configshowlocked'] = 'Show restricted activities as locked steps';
$string['configshowprogress'] = 'Show the "My progress" section (activities)';
$string['configshowtime'] = 'Show the "My time" section';
$string['configtitle'] = 'Block title';
$string['defaulttitle'] = 'My progress';
$string['donecount'] = '{$a} done';
$string['durationsource_field'] = 'Course custom field (recommended)';
$string['durationsource_manual'] = 'Set manually in this block';
$string['durationsource_none'] = 'No expected duration (show total time only)';
$string['efprogress:addinstance'] = 'Add a new "My progress" block';
$string['frac'] = '{$a->done} of {$a->total}';
$string['lockedcount'] = '{$a} locked';
$string['markslabel'] = 'Course activities';
$string['mytime'] = 'my time';
$string['nocompletion_hint'] = 'No activity in this course has completion configured and time is not being tracked, so students see nothing yet. Configure activity completion (or enable time tracking with the eLearning Fácil report) to bring this block to life.';
$string['notime_hint'] = 'This course is not tracking time with the eLearning Fácil report (local_efreport), so the "My time" section will stay hidden even if enabled.';
$string['orientation_auto'] = 'Auto (adapts to the block width)';
$string['orientation_horizontal'] = 'Horizontal (wide area, e.g. above the content)';
$string['orientation_vertical'] = 'Vertical (side column)';
$string['pendcount'] = '{$a} pending';
$string['pluginname'] = 'My progress';
$string['privacy:metadata'] = 'The My progress block only displays existing data (official Moodle activity completion and, when installed, the time measured by local_efreport). It does not store any personal data itself.';
$string['ringlabel_activities'] = 'Activities';
$string['ringlabel_time'] = 'Time · of {$a}';
$string['setting_cheer100'] = 'Default message at 100%';
$string['setting_cheer100_desc'] = 'Shown when the student completes every activity. Never says "course completed": course completion may depend on criteria the block does not see. Can be customised per course in the block settings.';
$string['setting_cheer60'] = 'Default message from 60%';
$string['setting_cheer60_desc'] = 'Encouragement shown from 60% of activities completed. Can be customised per course in the block settings.';
$string['setting_durationfield'] = 'Course custom field with the expected duration';
$string['setting_durationfield_desc'] = 'Short name of the course custom field that holds the expected duration (each client names it differently: duracion, horas…). Accepted values: number of hours ("30", "30 h", "7.5"), minutes ("90 min") or hh:mm.';
$string['showlocked_hint'] = 'Activities with access restrictions that the course page lists greyed out appear as a padlock, without a link and with the reason in the tooltip, and count towards the total. Hidden activities (or restrictions set to hide entirely) never appear.';
$string['state_done'] = 'done ✓';
$string['state_donefail'] = 'done, with a failing grade ✓';
$string['state_locked'] = 'locked 🔒';
$string['state_now'] = 'you are here';
$string['state_pend'] = 'pending';
$string['timeleft'] = 'you have {$a} left';
