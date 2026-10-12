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
