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
 * Data engine for the "My progress" block.
 *
 * @package    block_efprogress
 * @copyright  2026 Ana Rosiris Castro Mendez, eLearning Fácil (https://elearningfacil.com)
 * @author     Ana Rosiris Castro Mendez <info@elearningfacil.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_efprogress\local;

/**
 * Data engine of the block: activity completion and student time.
 *
 * It only READS data that already exists (Moodle's official completion and,
 * if local_efreport is installed, the measured time); it stores nothing of its own.
 *
 * @package    block_efprogress
 * @copyright  2026 Ana Rosiris Castro Mendez, eLearning Fácil (https://elearningfacil.com)
 * @author     Ana Rosiris Castro Mendez <info@elearningfacil.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class progress {
    /** @var string Done mark. */
    public const STATE_DONE = 'done';

    /** @var string "You are here" mark (next pending activity in course order). */
    public const STATE_NOW = 'now';

    /** @var string Pending mark. */
    public const STATE_PEND = 'pend';

    /** @var string Locked mark (restricted, shown greyed-out on the course page). */
    public const STATE_LOCKED = 'locked';

    /**
     * Course activities WITH completion configured and their state for the user.
     *
     * The list is live: it reflects whatever the course contains on each render.
     * The first non-completed accessible activity (in course order) is marked
     * as "you are here". Restricted activities that the course page lists
     * greyed-out (availability info shown) appear as "locked", and so do the
     * activities of a section listed greyed-out (they inherit the section's
     * reason), so the student sees the whole path and the percentage
     * denominator stays stable; truly hidden activities (or restrictions set
     * to hide entirely, own or of the section) never appear, and neither do
     * the activities restricted to ANOTHER group (see groupaccess).
     *
     * With a period window (the one the time report measures this student
     * with: course dates, group sheet or enrolment), an activity completed
     * OUTSIDE it is still marked as done but flagged 'outside', so the block
     * can show it toned down and leave it out of the count, exactly as the
     * report does. Without a window (no local_efreport) nothing changes.
     *
     * @param \stdClass $course Course.
     * @param int $userid User (the student themselves).
     * @param bool $showlocked Include restricted-but-listed activities as locked.
     * @param int $from Start of the period window (timestamp, 0 = no limit).
     * @param int $to End of the period window (timestamp, 0 = no limit).
     * @return array List of {cmid, name, url, state, reason, failed, outside} objects.
     */
    public static function activities(
        \stdClass $course,
        int $userid,
        bool $showlocked = true,
        int $from = 0,
        int $to = 0
    ): array {
        $completion = new \completion_info($course);
        if (!$completion->is_enabled()) {
            return [];
        }

        $modinfo = get_fast_modinfo($course, $userid);
        $out = [];
        $nowset = false;
        $usergroups = null;
        foreach ($modinfo->get_cms() as $cm) {
            if ($cm->deletioninprogress) {
                continue;
            }
            $locked = false;
            $reason = '';
            if (!$cm->uservisible) {
                // Activities of another group are not part of this student's
                // path: no padlock and they do not count in the total.
                $usergroups ??= groupaccess::user_groups((int) $course->id, $userid);
                if (groupaccess::is_foreign($cm, $usergroups)) {
                    continue;
                }
                if (!$showlocked) {
                    continue;
                }
                if ($cm->is_visible_on_course_page() && self::sections_listed($cm)) {
                    $reason = self::lock_reason($cm, $course);
                } else if ($cm->is_visible_on_course_page()) {
                    // Own restriction shown, but its section (or the subsection
                    // holding it) is hidden from the student: not on their path.
                    continue;
                } else {
                    // Not listed by itself: maybe only its parent section is
                    // restricted and shown greyed-out (typical sequential
                    // itinerary, one module per section).
                    $sectionreason = self::section_lock_reason($cm, $userid);
                    if ($sectionreason === null) {
                        continue;
                    }
                    $reason = $sectionreason;
                }
                $locked = true;
            }
            if ((int) $completion->is_enabled($cm) === COMPLETION_TRACKING_NONE) {
                continue;
            }
            $data = $completion->get_data($cm, true, $userid);
            // Done = any completion state, as the time report counts it: an
            // activity completed with a failing grade is done (the result is
            // another matter, flagged on the mark).
            $done = (int) $data->completionstate > COMPLETION_INCOMPLETE;
            $failed = in_array((int) $data->completionstate, [COMPLETION_COMPLETE_FAIL, COMPLETION_COMPLETE_FAIL_HIDDEN], true);
            // Completed outside the student's training period: the report
            // filters on this same date, so it does not count there either.
            $outside = $done && !self::in_window((int) $data->timemodified, $from, $to);
            // Done beats locked: what was completed before the restriction
            // closed (e.g. "until" dates) keeps counting as done.
            if ($done) {
                $state = self::STATE_DONE;
            } else if ($locked) {
                $state = self::STATE_LOCKED;
            } else if (!$nowset) {
                $state = self::STATE_NOW;
                $nowset = true;
            } else {
                $state = self::STATE_PEND;
            }
            $out[] = (object) [
                'cmid'  => (int) $cm->id,
                // Plain text: the renderer escapes it once (attributes, bubbles).
                'name'  => format_string($cm->name, true, ['context' => $cm->context, 'escape' => false]),
                'failed' => $failed,
                'outside' => $outside,
                'url'   => $locked ? null : ($cm->url ?: new \moodle_url(
                    '/course/view.php',
                    ['id' => $course->id],
                    'module-' . $cm->id
                )),
                'state' => $state,
                'reason' => $reason,
            ];
        }
        return $out;
    }

    /**
     * Whether a timestamp falls inside a period window (0 = open end).
     *
     * @param int $time Timestamp.
     * @param int $from Start (0 = no limit).
     * @param int $to End (0 = no limit).
     * @return bool
     */
    public static function in_window(int $time, int $from, int $to): bool {
        return ($from <= 0 || $time >= $from) && ($to <= 0 || $time <= $to);
    }

    /**
     * Plain-text reason why an activity is restricted, for the mark tooltip.
     *
     * @param \cm_info $cm Course module (not uservisible, availability info set).
     * @param \stdClass $course Course.
     * @return string Reason without HTML ('' if none).
     */
    public static function lock_reason(\cm_info $cm, \stdClass $course): string {
        return self::plain_reason($cm->availableinfo, $course);
    }

    /**
     * Reason inherited from a parent section listed greyed-out, or null if the
     * activity must stay hidden.
     *
     * The core leaves cm availableinfo EMPTY when the blocker is the section
     * ("that is already presented from the section"), so these activities fail
     * is_visible_on_course_page() even though the student does see the greyed
     * section on the course page. Null when the activity itself is hidden,
     * stealth, blocked by an own hide-entirely restriction, or the section is
     * hidden / entirely-hidden-restricted: the teacher's intent stands.
     *
     * @param \cm_info $cm Course module (not uservisible, no own info shown).
     * @param int $userid User.
     * @return string|null Plain-text section reason, or null to exclude.
     */
    public static function section_lock_reason(\cm_info $cm, int $userid): ?string {
        if (!$cm->visible || !$cm->visibleoncoursepage) {
            return null;
        }
        // The activity's OWN restrictions (if any) must not hide it: an own
        // failing hide-entirely restriction keeps it out.
        if (!empty($cm->availability)) {
            $information = '';
            $info = new \core_availability\info_module($cm);
            if (!$info->is_available($information, false, $userid, $cm->get_modinfo())) {
                return null;
            }
        }
        // Climb the sections: a delegated section (inside a subsection) is
        // blocked by the subsection module or by the page section holding it.
        $modinfo = $cm->get_modinfo();
        $section = $cm->get_section_info();
        for ($i = 0; $i < 6 && $section; $i++) {
            if (!$section->visible) {
                return null;
            }
            if (trim((string) $section->availableinfo) !== '') {
                return self::plain_reason($section->availableinfo, $cm->get_course());
            }
            if (!$section->is_delegated()) {
                return null;
            }
            $owner = $modinfo->instances['subsection'][(int) $section->itemid] ?? null;
            if ($owner === null || !$owner->visible || !$owner->visibleoncoursepage) {
                return null;
            }
            if (!$owner->uservisible && trim((string) $owner->availableinfo) !== '') {
                return self::plain_reason($owner->availableinfo, $cm->get_course());
            }
            $section = $modinfo->get_section_info($owner->sectionnum);
        }
        return null;
    }

    /**
     * Whether every section up from the activity (page section, and the
     * subsections holding it) is listed for the student: visible, and either
     * available or shown greyed-out. False when one is hidden or hidden
     * entirely by a restriction.
     *
     * @param \cm_info $cm Course module.
     * @return bool
     */
    public static function sections_listed(\cm_info $cm): bool {
        $modinfo = $cm->get_modinfo();
        $section = $cm->get_section_info();
        for ($i = 0; $i < 6 && $section; $i++) {
            if (!$section->visible || (!$section->uservisible && trim((string) $section->availableinfo) === '')) {
                return false;
            }
            if (!$section->is_delegated()) {
                return true;
            }
            $owner = $modinfo->instances['subsection'][(int) $section->itemid] ?? null;
            if (
                $owner === null || !$owner->visible || !$owner->visibleoncoursepage
                    || (!$owner->uservisible && trim((string) $owner->availableinfo) === '')
            ) {
                return false;
            }
            $section = $modinfo->get_section_info($owner->sectionnum);
        }
        return true;
    }

    /**
     * Formats availability information into plain text for the mark tooltip.
     *
     * @param string|\renderable $availableinfo Raw availability information.
     * @param \stdClass $course Course.
     * @return string Reason without HTML ('' if none).
     */
    protected static function plain_reason($availableinfo, \stdClass $course): string {
        $info = \core_availability\info::format_info($availableinfo, $course);
        return trim(html_to_text($info, 0, false));
    }

    /**
     * Whether the course measures time with local_efreport (soft dependency).
     *
     * @param int $courseid Course.
     * @return bool
     */
    public static function time_available(int $courseid): bool {
        if (!class_exists('\\local_efreport\\local\\timeapi')) {
            return false;
        }
        return \local_efreport\local\timeapi::is_tracking_enabled($courseid);
    }

    /**
     * Total time of the user in the course, in seconds (0 if there is no measurement).
     *
     * @param int $userid User.
     * @param int $courseid Course.
     * @return int Seconds.
     */
    public static function user_seconds(int $userid, int $courseid): int {
        if (!self::time_available($courseid)) {
            return 0;
        }
        $numbers = self::report_numbers(get_course($courseid), $userid);
        if ($numbers !== null) {
            return (int) $numbers->seconds;
        }
        return (int) \local_efreport\local\timeapi::get_course_time($userid, $courseid);
    }

    /**
     * The numbers the time report gives this student (completed, total,
     * seconds), measured with the period window the report uses for them
     * (course, group sheet or enrolment dates), so block and report agree.
     * Null without local_efreport 2.44 or later.
     *
     * @param \stdClass $course Course.
     * @param int $userid User.
     * @return \stdClass|null {completed, total, seconds, from, to}
     */
    public static function report_numbers(\stdClass $course, int $userid): ?\stdClass {
        if (!class_exists('\\local_efreport\\local\\progressapi')) {
            return null;
        }
        return \local_efreport\local\progressapi::student($course, $userid);
    }

    /**
     * Expected course duration in minutes according to the instance configuration.
     *
     * Sources: 'field' = course custom field (the shortname is chosen in the
     * block's site settings), 'manual' = set in the block,
     * 'none' = no duration. Returns 0 if there is no usable data.
     *
     * @param int $courseid Course.
     * @param \stdClass|null $config Block instance configuration.
     * @return int Minutes (0 = no duration).
     */
    public static function duration_minutes(int $courseid, ?\stdClass $config): int {
        $source = $config->durationsource ?? 'field';
        if ($source === 'none') {
            return 0;
        }
        if ($source === 'manual') {
            $hours = (int) ($config->durationhours ?? 0);
            $mins = (int) ($config->durationmins ?? 0);
            return max(0, $hours * 60 + $mins);
        }
        // Course custom field: the value is entered ONCE where clients already
        // enter it; the field name varies per client and is set in the block's
        // site settings.
        $shortname = trim((string) get_config('block_efprogress', 'durationfield'));
        if ($shortname === '') {
            return 0;
        }
        $handler = \core_customfield\handler::get_handler('core_course', 'course');
        foreach ($handler->get_instance_data($courseid, true) as $data) {
            if (
                \core_text::strtolower($data->get_field()->get('shortname'))
                    !== \core_text::strtolower($shortname)
            ) {
                continue;
            }
            return self::parse_duration((string) $data->get_value());
        }
        return 0;
    }

    /**
     * Parses the duration field value tolerantly.
     *
     * Accepts: a number (hours, decimal comma allowed), "30 h" / "30h" / "30 horas",
     * "90 min" / "90 m" (minutes), "hh:mm". Empty or unparsable → 0.
     *
     * @param string $raw Field value as is.
     * @return int Minutes.
     */
    public static function parse_duration(string $raw): int {
        $v = \core_text::strtolower(trim($raw));
        if ($v === '') {
            return 0;
        }
        // Format hh:mm.
        if (preg_match('/^(\d{1,4}):([0-5]?\d)$/', $v, $m)) {
            return (int) $m[1] * 60 + (int) $m[2];
        }
        $v = str_replace(',', '.', $v);
        // Number with optional suffix: hours by default; "min"/"m" = minutes.
        if (preg_match('/^(\d+(?:\.\d+)?)\s*(h|hs|hora|horas|min|mins|minuto|minutos|m)?\.?$/u', $v, $m)) {
            $num = (float) $m[1];
            if ($num <= 0) {
                return 0;
            }
            $unit = $m[2] ?? '';
            if (in_array($unit, ['min', 'mins', 'minuto', 'minutos', 'm'], true)) {
                return (int) round($num);
            }
            return (int) round($num * 60);
        }
        return 0;
    }

    /**
     * Formats minutes as "12h 40m" (or "40m" if under one hour).
     *
     * @param int $minutes Minutes.
     * @return string
     */
    public static function format_minutes(int $minutes): string {
        $minutes = max(0, $minutes);
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        if ($h > 0) {
            return $h . 'h ' . sprintf('%02d', $m) . 'm';
        }
        return $m . 'm';
    }

    /**
     * Cheer text based on the percentage (or empty string if not applicable).
     *
     * The text comes from the instance config if the manager customised it
     * for the course; otherwise from the site setting; otherwise from the
     * plugin string. It never says "course completed": the block does not see
     * all the criteria.
     *
     * @param int $pct Activity percentage (0-100).
     * @param \stdClass|null $config Instance configuration.
     * @return string Text already sanitised with format_string (empty if not applicable).
     */
    public static function cheer_text(int $pct, ?\stdClass $config): string {
        // Enabled by default: a freshly added block (no saved config) already
        // cheers; texts are hidden only if the manager explicitly unticked them.
        if (isset($config->showcheer) && empty($config->showcheer)) {
            return '';
        }
        if ($pct >= 100) {
            $key = 'cheer100';
        } else if ($pct >= 60) {
            $key = 'cheer60';
        } else {
            return '';
        }
        $text = trim((string) ($config->{$key} ?? ''));
        if ($text === '') {
            $text = trim((string) get_config('block_efprogress', $key));
        }
        if ($text === '') {
            $text = get_string($key . '_default', 'block_efprogress');
        }
        // Plain text: the renderer escapes it once.
        return format_string($text, true, ['escape' => false]);
    }
}
