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
 * "My progress" block: the student sees their own activity completion (ring +
 * marks grid) and their measured time (via local_efreport, soft dependency).
 *
 * @package    block_efprogress
 * @copyright  2026 Ana Rosiris Castro Mendez, eLearning Fácil (https://elearningfacil.com)
 * @author     Ana Rosiris Castro Mendez <info@elearningfacil.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_efprogress\local\progress;

/**
 * "My progress" block: activity ring + grid of marks + time.
 *
 * Shows ONLY the user's own data. Custom design (CSS ring with
 * conic-gradient + clickable marks), no JS.
 *
 * @package    block_efprogress
 * @copyright  2026 Ana Rosiris Castro Mendez, eLearning Fácil (https://elearningfacil.com)
 * @author     Ana Rosiris Castro Mendez <info@elearningfacil.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_efprogress extends block_base {
    /**
     * Initialises the block with its default title.
     */
    public function init() {
        $this->title = get_string('defaulttitle', 'block_efprogress');
    }

    /**
     * Allows changing the title per instance (config_title from the form).
     */
    public function specialization() {
        if (isset($this->config->title) && trim((string) $this->config->title) !== '') {
            $this->title = format_string($this->config->title);
        } else {
            $this->title = get_string('defaulttitle', 'block_efprogress');
        }
    }

    /**
     * Only inside a course (progress belongs to ONE specific course).
     *
     * @return array
     */
    public function applicable_formats() {
        return ['course-view' => true, 'site' => false, 'my' => false];
    }

    /**
     * Only one block per course.
     *
     * @return bool
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Has global settings (duration field + default cheer texts).
     *
     * @return bool
     */
    public function has_config() {
        return true;
    }

    /**
     * Builds the block content for the current user.
     *
     * @return \stdClass
     */
    public function get_content() {
        global $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new \stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        // Outside a course there is nothing to show (magic getter: compare id).
        if ((int) $this->page->course->id == SITEID) {
            return $this->content;
        }
        if (!isloggedin() || isguestuser()) {
            return $this->content;
        }

        $course = $this->page->course;

        // Only for those who HAVE progress: enrolled users that count towards
        // completion (students). Inspectors, managers or read-only roles do
        // not see the block (they have no progress of their own to show).
        if (!is_enrolled($this->page->context, $USER->id, 'moodle/course:isincompletionreports', true)) {
            // Whoever configures the course: a hint when nothing would show
            // the students (no completion and no measurement); nothing otherwise.
            if (
                has_capability('moodle/course:manageactivities', $this->page->context)
                    && !(new completion_info($course))->is_enabled()
                    && !progress::time_available((int) $course->id)
            ) {
                $this->content->text = html_writer::tag(
                    'p',
                    get_string('nocompletion_hint', 'block_efprogress'),
                    ['class' => 'text-muted small mb-0']
                );
            }
            return $this->content;
        }
        $userid = (int) $USER->id;
        $config = $this->config;
        $parts = [];

        // My time section FIRST (only if the course measures with efreport):
        // it is short, and with many activities it would be buried under the grid.
        $showtime = !isset($config->showtime) || !empty($config->showtime);
        if ($showtime && progress::time_available((int) $course->id)) {
            $parts[] = $this->render_time($course, $userid, $config);
        }

        // My progress section (activities with completion).
        $showprogress = !isset($config->showprogress) || !empty($config->showprogress);
        $showlocked = !isset($config->showlocked) || !empty($config->showlocked);
        // The report's numbers come first: their period window also decides
        // which completions the marks may count (see progress::activities()).
        $numbers = $showprogress ? progress::report_numbers($course, $userid) : null;
        $acts = $showprogress
            ? progress::activities($course, $userid, $showlocked, (int) ($numbers->from ?? 0), (int) ($numbers->to ?? 0))
            : [];
        if (!empty($acts)) {
            $parts[] = $this->render_progress($acts, $config, $numbers);
            $this->touch_tooltip_js();
        }

        if (empty($parts)) {
            // No activities with completion and no measurement: hint only for
            // those who can configure the course; empty block for the student (no noise).
            if (has_capability('moodle/course:manageactivities', $this->page->context)) {
                $this->content->text = html_writer::tag(
                    'p',
                    get_string('nocompletion_hint', 'block_efprogress'),
                    ['class' => 'text-muted small mb-0']
                );
            }
            return $this->content;
        }

        // Layout: the wrapper is the "container" of the CSS query; the
        // efp-o-* class enables forced horizontal, forced vertical or auto.
        $orientation = $config->orientation ?? 'auto';
        if (!in_array($orientation, ['auto', 'vertical', 'horizontal'], true)) {
            $orientation = 'auto';
        }
        $this->content->text = html_writer::div(
            html_writer::div(implode('', $parts), 'efprogress'),
            'efp-wrap efp-o-' . $orientation
        );
        return $this->content;
    }

    /**
     * On touch screens (no hover) the native tooltip does not exist: the first
     * tap on a mark shows a bubble with the activity name and the second tap
     * opens it. On desktop it does not interfere (hover still works).
     */
    protected function touch_tooltip_js(): void {
        $this->page->requires->js_amd_inline("
(function () {
    if (window.matchMedia && !window.matchMedia('(hover: none)').matches) { return; }
    var armed = null, bubble = null;
    function hide() {
        if (bubble) { bubble.remove(); bubble = null; }
        armed = null;
    }
    document.querySelectorAll('.block_efprogress .efp-mk').forEach(function (mk) {
        mk.addEventListener('click', function (e) {
            if (armed === mk) { hide(); return; }
            e.preventDefault();
            hide();
            armed = mk;
            bubble = document.createElement('div');
            bubble.className = 'efp-bubble';
            bubble.setAttribute('role', 'status');
            bubble.textContent = mk.getAttribute('aria-label');
            mk.parentNode.insertBefore(bubble, null);
            var r = mk.getBoundingClientRect(), p = mk.parentNode.getBoundingClientRect();
            bubble.style.left = Math.max(0, r.left - p.left + r.width / 2 - 110) + 'px';
            bubble.style.top = (r.bottom - p.top + 6) + 'px';
        });
    });
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.block_efprogress .efp-mk')) { hide(); }
    });
})();
");
    }

    /**
     * Activities section: ring, counters, cheer message and marks.
     *
     * @param array $acts Activities from progress::activities().
     * @param \stdClass|null $config Instance configuration.
     * @param \stdClass|null $numbers The time report's completed/total for this student (if installed).
     * @return string HTML.
     */
    protected function render_progress(array $acts, ?\stdClass $config, ?\stdClass $numbers = null): string {
        $total = count($acts);
        $done = 0;
        $locked = 0;
        $outside = 0;
        foreach ($acts as $a) {
            if ($a->state === progress::STATE_DONE) {
                // Done outside the training period: shown, never counted.
                if (!empty($a->outside)) {
                    $outside++;
                } else {
                    $done++;
                }
            } else if ($a->state === progress::STATE_LOCKED) {
                $locked++;
            }
        }
        if ($numbers !== null && (int) $numbers->total > 0) {
            // The ring says what the time report says (same denominator, same
            // period window); the marks keep showing the student's path.
            $done = (int) $numbers->completed;
            $total = (int) $numbers->total;
        }
        // Rounded down: 199 of 200 is not "100 %".
        $pct = (int) floor($done / max(1, $total) * 100);

        $ring = $this->ring(
            $pct,
            $pct . '%',
            get_string('frac', 'block_efprogress', (object) ['done' => $done, 'total' => $total]),
            get_string('ringlabel_activities', 'block_efprogress'),
            'green'
        );

        // The line always adds up to the ring's total.
        $pend = max(0, $total - $done - $locked - $outside);
        $counts = html_writer::tag('b', get_string('donecount', 'block_efprogress', $done))
            . ' · ' . get_string('pendcount', 'block_efprogress', $pend);
        if ($locked > 0) {
            $counts .= ' · ' . get_string('lockedcount', 'block_efprogress', $locked);
        }
        if ($outside > 0) {
            $counts .= ' · ' . get_string('outsidecount', 'block_efprogress', $outside);
        }
        $cheer = progress::cheer_text($pct, $config);
        if ($cheer !== '') {
            $counts .= html_writer::empty_tag('br') . s($cheer);
        }

        $marks = '';
        foreach ($acts as $a) {
            if (!empty($a->outside)) {
                $statekey = 'state_doneoutside';
            } else if (!empty($a->failed)) {
                $statekey = 'state_donefail';
            } else {
                $statekey = 'state_' . $a->state;
            }
            $statelabel = get_string($statekey, 'block_efprogress');
            $label = $a->name . ' — ' . $statelabel;
            $classes = 'efp-mk efp-' . $a->state . (!empty($a->failed) ? ' efp-failed' : '')
                . (!empty($a->outside) ? ' efp-outside' : '');
            if ($a->state === progress::STATE_LOCKED && $a->reason !== '') {
                $label .= ' · ' . $a->reason;
            }
            $symbol = $a->state === progress::STATE_DONE ? '✓'
                : ($a->state === progress::STATE_NOW ? '●'
                : ($a->state === progress::STATE_LOCKED ? '🔒' : '○'));
            if ($a->url) {
                $label .= ' · ' . get_string('clicktogo', 'block_efprogress');
                $marks .= html_writer::link($a->url, $symbol, [
                    'class'      => $classes,
                    'title'      => $label,
                    'role'       => 'listitem',
                    'aria-label' => $label,
                ]);
            } else {
                // Locked (or done but no longer accessible): mark without a
                // link; the tooltip / touch bubble carries the reason.
                $marks .= html_writer::span($symbol, $classes, [
                    'title'      => $label,
                    'role'       => 'listitem',
                    'aria-label' => $label,
                ]);
            }
        }

        $legend = html_writer::div(
            html_writer::span(html_writer::span('', 'efp-dot efp-dot-done')
                . get_string('state_done', 'block_efprogress'))
            . html_writer::span(html_writer::span('', 'efp-dot efp-dot-now')
                . get_string('state_now', 'block_efprogress'))
            . html_writer::span(html_writer::span('', 'efp-dot efp-dot-pend')
                . get_string('state_pend', 'block_efprogress'))
            . ($locked > 0
                ? html_writer::span(html_writer::span('', 'efp-dot efp-dot-locked')
                    . get_string('state_locked', 'block_efprogress'))
                : '')
            . ($outside > 0
                ? html_writer::span(html_writer::span('', 'efp-dot efp-dot-outside')
                    . get_string('state_outside', 'block_efprogress'))
                : ''),
            'efp-legend'
        );

        return html_writer::div(
            html_writer::div($ring, 'efp-rings')
            . html_writer::div($counts, 'efp-counts')
            . html_writer::div($marks, 'efp-marks', [
                'role'       => 'list',
                'aria-label' => get_string('markslabel', 'block_efprogress'),
            ])
            . $legend,
            'efp-section'
        );
    }

    /**
     * Time section: twin blue ring (with duration) or stat card (without it).
     *
     * @param \stdClass $course Course.
     * @param int $userid User.
     * @param \stdClass|null $config Instance configuration.
     * @return string HTML.
     */
    protected function render_time(\stdClass $course, int $userid, ?\stdClass $config): string {
        $myseconds = progress::user_seconds($userid, (int) $course->id);
        $mymin = intdiv($myseconds, 60);
        $goalmin = progress::duration_minutes((int) $course->id, $config);
        $mytime = progress::format_minutes($mymin);

        if ($goalmin > 0) {
            // Seconds against seconds, rounded down: 29h 59m 50s is not 100 %.
            $goalseconds = $goalmin * 60;
            $pct = (int) min(100, floor($myseconds / $goalseconds * 100));
            $sub = '';
            if ($myseconds < $goalseconds) {
                $sub = get_string(
                    'timeleft',
                    'block_efprogress',
                    progress::format_minutes((int) ceil(($goalseconds - $myseconds) / 60))
                );
            }
            $inner = $this->ring(
                $pct,
                $pct . '%',
                $mytime,
                get_string(
                    'ringlabel_time',
                    'block_efprogress',
                    progress::format_minutes($goalmin)
                ),
                'blue',
                $sub
            );
            return html_writer::div(html_writer::div($inner, 'efp-rings'), 'efp-section efp-time');
        }

        // No expected duration: card with the big figure, never long sentences.
        $chip = html_writer::div(
            html_writer::div(s($mytime), 'efp-big')
            . html_writer::div('⏱ ' . get_string('mytime', 'block_efprogress'), 'efp-cap'),
            'efp-statchip'
        );
        return html_writer::div($chip, 'efp-section efp-time');
    }

    /**
     * CSS ring (conic-gradient) with its equivalent text for screen readers.
     *
     * @param int $pct Percentage (0-100).
     * @param string $big Big text in the centre.
     * @param string $small Small text in the centre.
     * @param string $label Label under the ring.
     * @param string $color 'green' or 'blue'.
     * @param string $sub Extra line under the label (e.g. "2h left").
     * @return string HTML.
     */
    protected function ring(int $pct, string $big, string $small, string $label, string $color, string $sub = ''): string {
        $pct = max(0, min(100, $pct));
        $hex = ($color === 'blue') ? '#0f6cbf' : '#1e7e34';
        $style = "background: conic-gradient($hex 0 $pct%, var(--efp-track, #e8ecef) $pct% 100%);";
        $hole = html_writer::div(
            html_writer::div(s($big), 'efp-pct')
            . html_writer::div(s($small), 'efp-frac'),
            'efp-hole'
        );
        $aria = $label . ': ' . $big . ' (' . $small . ')' . ($sub !== '' ? ' · ' . $sub : '');
        return html_writer::div(
            html_writer::div($hole, 'efp-ring efp-ring-' . $color, [
                'style'      => $style,
                'role'       => 'img',
                'aria-label' => $aria,
            ])
            . html_writer::div(s($label), 'efp-rlabel')
            . ($sub !== '' ? html_writer::div(s($sub), 'efp-rsub') : ''),
            'efp-ringcol'
        );
    }
}
