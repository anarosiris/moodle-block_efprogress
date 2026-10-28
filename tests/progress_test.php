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
 * Tests for the block_efprogress data engine.
 *
 * @package    block_efprogress
 * @copyright  2026 Ana Rosiris Castro Mendez, eLearning Fácil (https://elearningfacil.com)
 * @author     Ana Rosiris Castro Mendez <info@elearningfacil.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_efprogress;

use block_efprogress\local\progress;

/**
 * Tests of the block engine (mark states, duration, cheer).
 *
 * @package    block_efprogress
 * @copyright  2026 Ana Rosiris Castro Mendez, eLearning Fácil (https://elearningfacil.com)
 * @author     Ana Rosiris Castro Mendez <info@elearningfacil.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \block_efprogress\local\progress
 */
final class progress_test extends \advanced_testcase {
    /**
     * Only activities with completion appear and the states are correct:
     * done ✓, the first non-done one = "you are here", the rest pending.
     */
    public function test_activities_states(): void {
        $this->resetAfterTest();
        $gen = $this->getDataGenerator();
        $course = $gen->create_course(['enablecompletion' => 1]);
        $student = $gen->create_user();
        $gen->enrol_user($student->id, $course->id, 'student');

        // 3 with manual completion + 1 WITHOUT completion (must not appear).
        $pages = [];
        for ($i = 0; $i < 3; $i++) {
            $pages[] = $gen->create_module(
                'page',
                ['course' => $course->id],
                ['completion' => COMPLETION_TRACKING_MANUAL]
            );
        }
        $gen->create_module(
            'page',
            ['course' => $course->id],
            ['completion' => COMPLETION_TRACKING_NONE]
        );

        // The student completes the first one.
        $completion = new \completion_info($course);
        $cm0 = get_fast_modinfo($course)->get_cm($pages[0]->cmid);
        $completion->update_state($cm0, COMPLETION_COMPLETE, (int) $student->id);

        $acts = progress::activities($course, (int) $student->id);
        $this->assertCount(3, $acts); // The activity without completion is left out.
        $this->assertSame(progress::STATE_DONE, $acts[0]->state);
        $this->assertSame(progress::STATE_NOW, $acts[1]->state); // First pending one.
        $this->assertSame(progress::STATE_PEND, $acts[2]->state);
        $this->assertNotEmpty($acts[0]->url);
    }

    /**
     * Restricted activities listed greyed-out on the course page appear as
     * locked (no link, with reason); truly hidden ones never appear; the
     * instance setting can switch locked marks off.
     */
    public function test_locked_activities(): void {
        $this->resetAfterTest();
        set_config('enableavailability', 1);
        $gen = $this->getDataGenerator();
        $course = $gen->create_course(['enablecompletion' => 1]);
        $student = $gen->create_user();
        $gen->enrol_user($student->id, $course->id, 'student');

        $future = time() + WEEKSECS;
        $restriction = static function (bool $shown) use ($future): string {
            return json_encode([
                'op' => '&',
                'c' => [['type' => 'date', 'd' => '>=', 't' => $future]],
                'showc' => [$shown],
            ]);
        };

        // Accessible + restricted-shown + restricted-hidden + hidden.
        $gen->create_module(
            'page',
            ['course' => $course->id],
            ['completion' => COMPLETION_TRACKING_MANUAL]
        );
        $gen->create_module(
            'page',
            ['course' => $course->id, 'availability' => $restriction(true)],
            ['completion' => COMPLETION_TRACKING_MANUAL]
        );
        $gen->create_module(
            'page',
            ['course' => $course->id, 'availability' => $restriction(false)],
            ['completion' => COMPLETION_TRACKING_MANUAL]
        );
        $gen->create_module(
            'page',
            ['course' => $course->id, 'visible' => 0],
            ['completion' => COMPLETION_TRACKING_MANUAL]
        );

        $acts = progress::activities($course, (int) $student->id);
        $this->assertCount(2, $acts); // Hidden ones are out, greyed-out one is in.
        $this->assertSame(progress::STATE_NOW, $acts[0]->state);
        $this->assertSame(progress::STATE_LOCKED, $acts[1]->state);
        $this->assertNull($acts[1]->url); // No link: the student cannot enter yet.
        $this->assertNotSame('', $acts[1]->reason); // The tooltip explains why.

        // Instance setting off: only accessible activities, as before.
        $acts = progress::activities($course, (int) $student->id, false);
        $this->assertCount(1, $acts);
        $this->assertSame(progress::STATE_NOW, $acts[0]->state);
    }

    /**
     * Activities inside a section listed greyed-out inherit the section's
     * reason as locked marks; hidden sections (or hide-entirely section
     * restrictions), teacher-hidden activities and own hide-entirely
     * restrictions keep them out; an own shown restriction wins the reason.
     */
    public function test_section_locked_activities(): void {
        global $DB;
        $this->resetAfterTest();
        set_config('enableavailability', 1);
        $gen = $this->getDataGenerator();
        $course = $gen->create_course(
            ['enablecompletion' => 1, 'numsections' => 3],
            ['createsections' => true]
        );
        $student = $gen->create_user();
        $gen->enrol_user($student->id, $course->id, 'student');

        // Distinct future dates to tell the reasons apart (positive-offset
        // timezones keep the year stable): section 2030, own restriction 2035.
        $restriction = static function (bool $shown, int $t): string {
            return json_encode([
                'op' => '&',
                'c' => [['type' => 'date', 'd' => '>=', 't' => $t]],
                'showc' => [$shown],
            ]);
        };
        $t2030 = 1893456000;
        $t2035 = 2051222400;

        // Section 1 greyed-out; section 2 restricted-and-hidden entirely.
        $DB->set_field(
            'course_sections',
            'availability',
            $restriction(true, $t2030),
            ['course' => $course->id, 'section' => 1]
        );
        $DB->set_field(
            'course_sections',
            'availability',
            $restriction(false, $t2030),
            ['course' => $course->id, 'section' => 2]
        );
        rebuild_course_cache($course->id, true);

        // Section 0: accessible. Section 1: normal + teacher-hidden + own
        // hide-entirely + own shown restriction. Section 2: normal.
        $gen->create_module(
            'page',
            ['course' => $course->id],
            ['completion' => COMPLETION_TRACKING_MANUAL]
        );
        $gen->create_module(
            'page',
            ['course' => $course->id, 'section' => 1],
            ['completion' => COMPLETION_TRACKING_MANUAL]
        );
        $gen->create_module(
            'page',
            ['course' => $course->id, 'section' => 1, 'visible' => 0],
            ['completion' => COMPLETION_TRACKING_MANUAL]
        );
        $gen->create_module(
            'page',
            ['course' => $course->id, 'section' => 1, 'availability' => $restriction(false, $t2035)],
            ['completion' => COMPLETION_TRACKING_MANUAL]
        );
        $gen->create_module(
            'page',
            ['course' => $course->id, 'section' => 1, 'availability' => $restriction(true, $t2035)],
            ['completion' => COMPLETION_TRACKING_MANUAL]
        );
        $gen->create_module(
            'page',
            ['course' => $course->id, 'section' => 2],
            ['completion' => COMPLETION_TRACKING_MANUAL]
        );

        $acts = progress::activities($course, (int) $student->id);
        $this->assertCount(3, $acts);
        $this->assertSame(progress::STATE_NOW, $acts[0]->state);
        $this->assertSame(progress::STATE_LOCKED, $acts[1]->state);
        $this->assertStringContainsString('2030', $acts[1]->reason); // Section reason inherited.
        $this->assertSame(progress::STATE_LOCKED, $acts[2]->state);
        $this->assertStringContainsString('2035', $acts[2]->reason); // Own shown reason wins.

        // Setting off: only the accessible activity remains.
        $acts = progress::activities($course, (int) $student->id, false);
        $this->assertCount(1, $acts);
    }

    /**
     * The student's manual completion ("mark as done") is reflected instantly.
     */
    public function test_manual_completion_reflected(): void {
        $this->resetAfterTest();
        $gen = $this->getDataGenerator();
        $course = $gen->create_course(['enablecompletion' => 1]);
        $student = $gen->create_user();
        $gen->enrol_user($student->id, $course->id, 'student');
        $page = $gen->create_module(
            'page',
            ['course' => $course->id],
            ['completion' => COMPLETION_TRACKING_MANUAL]
        );

        $acts = progress::activities($course, (int) $student->id);
        $this->assertSame(progress::STATE_NOW, $acts[0]->state);

        $completion = new \completion_info($course);
        $cm = get_fast_modinfo($course)->get_cm($page->cmid);
        $completion->update_state($cm, COMPLETION_COMPLETE, (int) $student->id);

        $acts = progress::activities($course, (int) $student->id);
        $this->assertSame(progress::STATE_DONE, $acts[0]->state);
    }

    /**
     * Completed with a failing grade is done (as the time report counts it),
     * flagged so the student is not misled.
     */
    public function test_completed_with_fail_is_done(): void {
        global $DB;
        $this->resetAfterTest();
        $gen = $this->getDataGenerator();
        $course = $gen->create_course(['enablecompletion' => 1]);
        $student = $gen->create_user();
        $gen->enrol_user($student->id, $course->id, 'student');
        $quiz = $gen->create_module('quiz', ['course' => $course->id], [
            'completion' => COMPLETION_TRACKING_AUTOMATIC, 'completionpassgrade' => 1, 'completionusegrade' => 1,
        ]);
        $DB->insert_record('course_modules_completion', (object) [
            'coursemoduleid' => $quiz->cmid, 'userid' => $student->id, 'completionstate' => COMPLETION_COMPLETE_FAIL,
            'timemodified' => time(),
        ]);
        \cache::make('core', 'completion')->purge();
        $acts = progress::activities($course, (int) $student->id);
        $this->assertSame(progress::STATE_DONE, $acts[0]->state);
        $this->assertTrue($acts[0]->failed);
    }

    /**
     * Activities inside a restricted subsection: locked with the subsection's
     * reason when it is greyed-out, out when it is hidden entirely; an
     * activity with its own shown restriction inside a hidden section is out.
     */
    public function test_subsections_and_hidden_sections(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('enableavailability', 1);
        $gen = $this->getDataGenerator();
        $course = $gen->create_course(['enablecompletion' => 1, 'numsections' => 3], ['createsections' => true]);
        $student = $gen->create_user();
        $gen->enrol_user($student->id, $course->id, 'student');
        $restriction = static fn(bool $shown): string => json_encode([
            'op' => '&', 'c' => [['type' => 'date', 'd' => '>=', 't' => 2051222400]], 'showc' => [$shown],
        ]);

        // Section 1: a greyed-out subsection with two pages inside.
        $sub = $gen->create_module('subsection', ['course' => $course->id, 'section' => 1, 'availability' => $restriction(true)]);
        $inner = get_fast_modinfo($course)->get_cm($sub->cmid)->get_delegated_section_info();
        for ($i = 0; $i < 2; $i++) {
            $gen->create_module(
                'page',
                ['course' => $course->id, 'section' => $inner->sectionnum],
                ['completion' => COMPLETION_TRACKING_MANUAL]
            );
        }
        // Section 2: a hidden-entirely subsection with one page.
        $hidden = $gen->create_module('subsection', ['course' => $course->id, 'section' => 2,
            'availability' => $restriction(false)]);
        $inner2 = get_fast_modinfo($course)->get_cm($hidden->cmid)->get_delegated_section_info();
        $gen->create_module(
            'page',
            ['course' => $course->id, 'section' => $inner2->sectionnum],
            ['completion' => COMPLETION_TRACKING_MANUAL]
        );
        // Section 3 hidden from students, with a page carrying its own shown restriction.
        $DB->set_field('course_sections', 'visible', 0, ['course' => $course->id, 'section' => 3]);
        $gen->create_module(
            'page',
            ['course' => $course->id, 'section' => 3, 'availability' => $restriction(true)],
            ['completion' => COMPLETION_TRACKING_MANUAL]
        );
        // Section 0: one accessible page.
        $gen->create_module('page', ['course' => $course->id, 'section' => 0], ['completion' => COMPLETION_TRACKING_MANUAL]);
        rebuild_course_cache($course->id, true);

        $acts = progress::activities(get_course($course->id), (int) $student->id);
        // The accessible one and the two inside the greyed-out subsection (locked, with its reason).
        $this->assertCount(3, $acts);
        $states = array_map(static fn($a) => $a->state, $acts);
        $this->assertSame([progress::STATE_NOW, progress::STATE_LOCKED, progress::STATE_LOCKED], $states);
        $this->assertNotSame('', $acts[1]->reason);
        $this->assertSame($acts[1]->reason, $acts[2]->reason);
    }

    /**
     * With local_efreport installed, the numbers of the ring are the report's
     * (hidden activities with completion count in its total; completions
     * outside the student's period do not), and the marks follow the same
     * period window: a completion outside it is flagged, never counted.
     */
    public function test_report_numbers(): void {
        if (!class_exists('\\local_efreport\\local\\progressapi')) {
            $this->markTestSkipped('local_efreport is not installed.');
        }
        global $DB;
        $this->resetAfterTest();
        $gen = $this->getDataGenerator();
        $course = $gen->create_course(['enablecompletion' => 1,
            'startdate' => time() - 10 * DAYSECS, 'enddate' => time() + 10 * DAYSECS]);
        $student = $gen->create_user();
        $gen->enrol_user($student->id, $course->id, 'student');
        $p1 = $gen->create_module('page', ['course' => $course->id], ['completion' => COMPLETION_TRACKING_MANUAL]);
        $p2 = $gen->create_module('page', ['course' => $course->id], ['completion' => COMPLETION_TRACKING_MANUAL]);
        $gen->create_module('page', ['course' => $course->id, 'visible' => 0], ['completion' => COMPLETION_TRACKING_MANUAL]);
        $completion = new \completion_info($course);
        $modinfo = get_fast_modinfo($course);
        $completion->update_state($modinfo->get_cm($p1->cmid), COMPLETION_COMPLETE, (int) $student->id);
        $completion->update_state($modinfo->get_cm($p2->cmid), COMPLETION_COMPLETE, (int) $student->id);
        // The second one completed before the course period: outside the window.
        $DB->set_field(
            'course_modules_completion',
            'timemodified',
            time() - 20 * DAYSECS,
            ['coursemoduleid' => $p2->cmid, 'userid' => $student->id]
        );
        \local_efreport\local\progressapi::reset_caches();
        $numbers = progress::report_numbers(get_course($course->id), (int) $student->id);
        $this->assertSame(3, $numbers->total);
        $this->assertSame(1, $numbers->completed);
        $this->assertGreaterThan(0, $numbers->from);
        // Without a window the block's own list has two visible activities, both done.
        $acts = progress::activities(get_course($course->id), (int) $student->id);
        $this->assertCount(2, $acts);
        $this->assertFalse($acts[0]->outside);
        $this->assertFalse($acts[1]->outside);
        // With the report's window the second one is done but outside the period.
        $acts = progress::activities(get_course($course->id), (int) $student->id, true, $numbers->from, $numbers->to);
        $this->assertCount(2, $acts);
        $this->assertSame(progress::STATE_DONE, $acts[0]->state);
        $this->assertFalse($acts[0]->outside);
        $this->assertSame(progress::STATE_DONE, $acts[1]->state);
        $this->assertTrue($acts[1]->outside);
        $this->assertSame(1, count(array_filter($acts, fn($a) => $a->state === progress::STATE_DONE && !$a->outside)));
    }

    /**
     * The window helper: 0 means no limit on that side.
     */
    public function test_in_window(): void {
        $this->assertTrue(progress::in_window(100, 0, 0));
        $this->assertTrue(progress::in_window(100, 100, 100));
        $this->assertTrue(progress::in_window(100, 50, 0));
        $this->assertTrue(progress::in_window(100, 0, 150));
        $this->assertFalse(progress::in_window(100, 101, 0));
        $this->assertFalse(progress::in_window(100, 0, 99));
    }

    /**
     * Tolerant parsing of the duration field (odd client formats).
     */
    public function test_parse_duration(): void {
        $this->assertSame(1800, progress::parse_duration('30'));       // Hours.
        $this->assertSame(1800, progress::parse_duration('30 h'));
        $this->assertSame(1800, progress::parse_duration('30h'));
        $this->assertSame(1800, progress::parse_duration('30 horas'));
        $this->assertSame(450, progress::parse_duration('7,5'));       // Decimal comma.
        $this->assertSame(90, progress::parse_duration('90 min'));     // Minutes.
        $this->assertSame(90, progress::parse_duration('1:30'));       // Hh:mm.
        $this->assertSame(0, progress::parse_duration(''));            // Empty.
        $this->assertSame(0, progress::parse_duration('treinta'));     // Non-numeric.
        $this->assertSame(0, progress::parse_duration('0'));
    }

    /**
     * Duration by instance configuration: manual, none and course field.
     */
    public function test_duration_minutes(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();

        $manual = (object) ['durationsource' => 'manual', 'durationhours' => 2, 'durationmins' => 30];
        $this->assertSame(150, progress::duration_minutes((int) $course->id, $manual));

        $none = (object) ['durationsource' => 'none'];
        $this->assertSame(0, progress::duration_minutes((int) $course->id, $none));

        // Course field: no field configured at site level → 0 (no error).
        set_config('durationfield', '', 'block_efprogress');
        $field = (object) ['durationsource' => 'field'];
        $this->assertSame(0, progress::duration_minutes((int) $course->id, $field));
    }

    /**
     * Cheer messages: thresholds, per-course customisation and deactivation.
     */
    public function test_cheer_text(): void {
        $this->resetAfterTest();
        // No saved config (freshly added block): enabled by default.
        $this->assertSame(
            get_string('cheer60_default', 'block_efprogress'),
            progress::cheer_text(60, null)
        );
        $on = (object) ['showcheer' => 1];
        $this->assertSame('', progress::cheer_text(59, $on));
        $this->assertSame(
            get_string('cheer60_default', 'block_efprogress'),
            progress::cheer_text(60, $on)
        );
        $this->assertSame(
            get_string('cheer100_default', 'block_efprogress'),
            progress::cheer_text(100, $on)
        );

        // Site text and course text (the course one wins).
        set_config('cheer100', 'Enhorabuena del sitio', 'block_efprogress');
        $this->assertSame('Enhorabuena del sitio', progress::cheer_text(100, $on));
        $custom = (object) ['showcheer' => 1, 'cheer100' => 'Lo lograste, campeón'];
        $this->assertSame('Lo lograste, campeón', progress::cheer_text(100, $custom));

        // Disabled per instance.
        $off = (object) ['showcheer' => 0];
        $this->assertSame('', progress::cheer_text(100, $off));
    }

    /**
     * Minute formatting "12h 40m".
     */
    public function test_format_minutes(): void {
        $this->assertSame('12h 40m', progress::format_minutes(760));
        $this->assertSame('30h 00m', progress::format_minutes(1800));
        $this->assertSame('40m', progress::format_minutes(40));
        $this->assertSame('0m', progress::format_minutes(0));
    }
}
