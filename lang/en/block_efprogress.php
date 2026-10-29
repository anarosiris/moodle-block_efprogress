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
$string['checkfiles_fix'] = 'Reinstall the plugin from its zip. If the problem persists, extract the zip with your hosting provider\'s file manager instead of the web installer, then purge all caches.';
$string['checkfiles_missing'] = 'missing';
$string['checkfiles_nomanifest'] = 'No integrity manifest found (development install): nothing to verify.';
$string['checkfiles_ok'] = 'All {$a} plugin files are present and intact.';
$string['cheer100_default'] = '🎉 All activities completed!';
$string['cheer60_default'] = 'Keep going, you\'re almost there!';
$string['cheerdefault'] = 'Leave blank to use the site default: "{$a}"';
$string['clicktogo'] = 'click to open';
$string['completioncount'] = 'This course has {$a} activity(ies) with completion tracking configured. Only those appear in the block; the list updates automatically when activities are added or removed.';
$string['configcheer100'] = 'Message at 100%';
$string['configcheer60'] = 'Message from 60% onwards';
$string['configdurationmanual'] = 'Expected duration (manual)';
$string['configdurationmanual_error'] = 'Enter whole hours (0 or more) and minutes from 0 to 59.';
$string['configdurationsource'] = 'Source of the expected course duration';
$string['configorientation'] = 'Layout';
$string['configshowcheer'] = 'Show encouragement messages';
$string['configshowlocked'] = 'Show restricted activities as locked steps';
$string['configshowprogress'] = 'Show the "My Progress" section (activities)';
$string['configshowtime'] = 'Show the "My time" section';
$string['configtitle'] = 'Block title';
$string['defaulttitle'] = 'My Progress';
$string['donecount'] = '{$a} done';
$string['durationsource_field'] = 'Course custom field (recommended)';
$string['durationsource_manual'] = 'Set manually in this block';
$string['durationsource_none'] = 'No expected duration (show total time only)';
$string['efprogress:addinstance'] = 'Add a new My Progress block';
$string['frac'] = '{$a->done} of {$a->total}';
$string['lockedcount'] = '{$a} locked';
$string['markslabel'] = 'Course activities';
$string['mytime'] = 'My time';
$string['nocompletion_hint'] = 'No activity in this course has completion tracking configured and time is not being tracked, so students will see nothing yet. Set up activity completion (or enable time tracking with the Training Evidence Report) so that the block has something to show.';
$string['notime_hint'] = 'This course is not tracking time with the Training Evidence Report (local_efreport), so the "My time" section will remain hidden even if it is enabled.';
$string['orientation_auto'] = 'Auto (adapts to the block width)';
$string['orientation_horizontal'] = 'Horizontal (wide region, e.g. above the course content)';
$string['orientation_vertical'] = 'Vertical (side column)';
$string['outsidecount'] = '{$a} outside the period';
$string['pendcount'] = '{$a} pending';
$string['pluginname'] = 'Training Evidence: My Progress';
$string['privacy:metadata'] = 'The My Progress block only displays existing data (Moodle\'s own activity completion and, when installed, the time measured by local_efreport). It does not store any personal data itself.';
$string['ringlabel_activities'] = 'Activities';
$string['ringlabel_time'] = 'Time · out of {$a}';
$string['setting_cheer100'] = 'Default message at 100%';
$string['setting_cheer100_desc'] = 'Shown when the student has completed every activity. It deliberately does not say "course completed", because course completion may depend on criteria the block cannot see. It can be customised per course in the block settings.';
$string['setting_cheer60'] = 'Default message from 60% onwards';
$string['setting_cheer60_desc'] = 'Encouragement shown once 60% of the activities have been completed. It can be customised per course in the block settings.';
$string['setting_durationfield'] = 'Course custom field holding the expected duration';
$string['setting_durationfield_desc'] = 'Short name of the course custom field that holds the expected duration (the name varies from site to site, e.g. duration, hours, duracion…). Accepted values: a number of hours ("30", "30 h", "7.5"), minutes ("90 min") or hh:mm.';
$string['showlocked_hint'] = 'Activities with access restrictions that the course page shows greyed out appear as a padlock, with no link and with the reason in the tooltip, and they count towards the total. Hidden activities (or restrictions set to hide the activity entirely) never appear.';
$string['state_done'] = 'done ✓';
$string['state_donefail'] = 'done, with a failing grade ✓';
$string['state_doneoutside'] = 'done outside your training period ✓ (not counted)';
$string['state_locked'] = 'locked 🔒';
$string['state_now'] = 'you are here';
$string['state_outside'] = 'done, outside the period';
$string['state_pend'] = 'pending';
$string['timeleft'] = 'you have {$a} left';
