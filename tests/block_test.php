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

namespace block_efprogress;

/**
 * Negative tests of the block itself: it only renders for an enrolled student
 * (whoever has progress of their own); guests, visitors and read-only roles
 * get an empty block.
 *
 * @package    block_efprogress
 * @copyright  2026 Ana Rosiris Castro Mendez, eLearning Fácil (https://elearningfacil.com)
 * @author     Ana Rosiris Castro Mendez <info@elearningfacil.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversNothing]
final class block_test extends \advanced_testcase {
    /**
     * Builds the block instance in a course page.
     *
     * @param \stdClass $course Course.
     * @return array [\moodle_page, \stdClass instance]
     */
    private function make_block(\stdClass $course): array {
        global $DB;
        $context = \context_course::instance($course->id);
        $page = new \moodle_page();
        $page->set_course($course);
        $page->set_context($context);
        $page->set_pagelayout('course');
        $page->set_pagetype('course-view-' . $course->format);
        $page->blocks->add_region('side-pre');
        $page->blocks->add_block('efprogress', 'side-pre', 0, false, 'course-view-*');
        $instance = $DB->get_record('block_instances', ['blockname' => 'efprogress', 'parentcontextid' => $context->id]);
        return [$page, $instance];
    }

    /**
     * With local_efreport installed, a completion outside the student's
     * training period is drawn as a toned-down mark and the counter line
     * (done, pending, outside) adds up to the ring's total, so the grid and
     * the ring never disagree (bug: "21 marks done, counter says 6").
     */
    public function test_completion_outside_period_is_shown_but_not_counted(): void {
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
        $pages = [];
        for ($i = 0; $i < 4; $i++) {
            $pages[] = $gen->create_module('page', ['course' => $course->id], ['completion' => COMPLETION_TRACKING_MANUAL]);
        }
        $completion = new \completion_info($course);
        $modinfo = get_fast_modinfo($course);
        foreach ([0, 1, 2] as $i) {
            $completion->update_state($modinfo->get_cm($pages[$i]->cmid), COMPLETION_COMPLETE, (int) $student->id);
        }
        // Two of the three completions happened before the course period.
        foreach ([1, 2] as $i) {
            $DB->set_field(
                'course_modules_completion',
                'timemodified',
                time() - 20 * DAYSECS,
                ['coursemoduleid' => $pages[$i]->cmid, 'userid' => $student->id]
            );
        }
        \local_efreport\local\progressapi::reset_caches();
        [$page, $instance] = $this->make_block($course);
        $this->setUser($student);
        $html = block_instance('efprogress', $instance, $page)->get_content()->text;
        // Ring: 1 of 4 (the report's numbers); line: 1 done · 1 pending · 2 outside.
        $this->assertStringContainsString('1 of 4', $html);
        $this->assertStringContainsString('<b>1 done</b> · 1 pending · 2 outside the period', $html);
        $this->assertSame(2, substr_count($html, 'efp-outside'), 'two toned-down marks');
        $this->assertSame(1, substr_count($html, 'efp-mk efp-done"'), 'one plain done mark');
        $this->assertStringContainsString('done outside your training period', $html);
        $this->assertStringContainsString('efp-dot-outside', $html);
        // The "you are here" mark lands on the pending activity, not on a finished one.
        $this->assertSame(1, substr_count($html, 'efp-now'));
    }

    /**
     * A student sees their progress; a visitor who is not logged in, a
     * non-editing teacher (no progress of their own) and a student of another
     * course see nothing.
     */
    public function test_only_enrolled_students_see_the_block(): void {
        $this->resetAfterTest();
        $gen = $this->getDataGenerator();
        $course = $gen->create_course(['enablecompletion' => 1]);
        $gen->create_module('page', ['course' => $course->id], ['completion' => COMPLETION_TRACKING_MANUAL]);
        $student = $gen->create_and_enrol($course, 'student');
        $teacher = $gen->create_and_enrol($course, 'teacher');
        $outsider = $gen->create_user();
        [$page, $instance] = $this->make_block($course);

        $this->setUser($student);
        $this->assertNotSame('', block_instance('efprogress', $instance, $page)->get_content()->text);

        $this->setUser(0);
        $this->assertSame('', block_instance('efprogress', $instance, $page)->get_content()->text);
        $this->setGuestUser();
        $this->assertSame('', block_instance('efprogress', $instance, $page)->get_content()->text);
        $this->setUser($teacher);
        $this->assertSame('', block_instance('efprogress', $instance, $page)->get_content()->text);
        $this->setUser($outsider);
        $this->assertSame('', block_instance('efprogress', $instance, $page)->get_content()->text);
    }
}
