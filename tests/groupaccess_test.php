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
 * Tests for the activities of other groups in the progress bar.
 *
 * @package    block_efprogress
 * @copyright  2026 Ana Rosiris Castro Mendez, eLearning Fácil (https://elearningfacil.com)
 * @author     Ana Rosiris Castro Mendez <info@elearningfacil.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_efprogress;

use block_efprogress\local\groupaccess;
use block_efprogress\local\progress;

/**
 * Activities restricted to another group are not part of the student's path.
 *
 * @package    block_efprogress
 * @copyright  2026 Ana Rosiris Castro Mendez, eLearning Fácil (https://elearningfacil.com)
 * @author     Ana Rosiris Castro Mendez <info@elearningfacil.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\block_efprogress\local\groupaccess::class)]
final class groupaccess_test extends \advanced_testcase {
    /**
     * Availability tree with one root condition.
     *
     * @param array $condition Condition.
     * @param string $op Root operator.
     * @return string JSON.
     */
    private static function tree(array $condition, string $op = '&'): string {
        $key = in_array($op, ['&', '!|'], true) ? 'showc' : 'show';
        return json_encode(['op' => $op, 'c' => [$condition], $key => $key === 'showc' ? [true] : true]);
    }

    /**
     * Only plain root "all of" group/grouping conditions leave a student out.
     */
    public function test_excluded_by_group(): void {
        $group5 = self::tree(['type' => 'group', 'id' => 5]);
        $this->assertTrue(groupaccess::excluded_by_group($group5, [7], []));
        $this->assertFalse(groupaccess::excluded_by_group($group5, [5], []));
        $this->assertTrue(groupaccess::excluded_by_group(self::tree(['type' => 'group']), [], []));
        $this->assertFalse(groupaccess::excluded_by_group(self::tree(['type' => 'group']), [2], []));
        $this->assertTrue(groupaccess::excluded_by_group(self::tree(['type' => 'grouping', 'id' => 9]), [5], [8]));
        $this->assertFalse(groupaccess::excluded_by_group(self::tree(['type' => 'group', 'id' => 5], '|'), [7], []));
        $this->assertFalse(groupaccess::excluded_by_group(self::tree(['type' => 'group', 'id' => 5], '!&'), [7], []));
        $this->assertFalse(groupaccess::excluded_by_group(null, [], []));
    }

    /**
     * Path of each student: foreign activities disappear, date-locked ones stay locked.
     */
    public function test_activities_per_group(): void {
        global $DB;
        $this->resetAfterTest();
        $gen = $this->getDataGenerator();
        $course = $gen->create_course(['enablecompletion' => 1, 'numsections' => 2]);
        $groupa = $gen->create_group(['courseid' => $course->id]);
        $groupb = $gen->create_group(['courseid' => $course->id]);
        $act1 = $gen->create_module('page', ['course' => $course->id, 'completion' => 1, 'section' => 1]);
        $act2 = $gen->create_module('page', ['course' => $course->id, 'completion' => 1, 'section' => 1,
            'availability' => self::tree(['type' => 'group', 'id' => (int) $groupa->id])]);
        $act3 = $gen->create_module('page', ['course' => $course->id, 'completion' => 1, 'section' => 2]);
        $act4 = $gen->create_module('page', ['course' => $course->id, 'completion' => 1, 'section' => 1,
            'availability' => self::tree(['type' => 'date', 'd' => '>=', 't' => time() + WEEKSECS])]);
        $DB->set_field(
            'course_sections',
            'availability',
            self::tree(['type' => 'group', 'id' => (int) $groupb->id]),
            ['course' => $course->id, 'section' => 2]
        );
        rebuild_course_cache($course->id, true);

        $users = [];
        foreach (['a', 'b', 'c'] as $key) {
            $users[$key] = $gen->create_user();
            $gen->enrol_user($users[$key]->id, $course->id, 'student');
        }
        $gen->create_group_member(['groupid' => $groupa->id, 'userid' => $users['a']->id]);
        $gen->create_group_member(['groupid' => $groupb->id, 'userid' => $users['b']->id]);

        $ids = function (\stdClass $user) use ($course): array {
            $this->setUser($user);
            $out = [];
            foreach (progress::activities($course, (int) $user->id, true) as $a) {
                $out[$a->cmid] = $a->state;
            }
            return $out;
        };

        $a = $ids($users['a']);
        $this->assertArrayHasKey($act2->cmid, $a);
        $this->assertArrayNotHasKey($act3->cmid, $a);
        $this->assertSame(progress::STATE_LOCKED, $a[$act4->cmid]);

        $b = $ids($users['b']);
        $this->assertArrayNotHasKey($act2->cmid, $b);
        $this->assertArrayHasKey($act3->cmid, $b);
        $this->assertSame(progress::STATE_LOCKED, $b[$act4->cmid]);

        $c = $ids($users['c']);
        $this->assertSame([(int) $act1->cmid, (int) $act4->cmid], array_keys($c));
    }
}
