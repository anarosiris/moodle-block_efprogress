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
 * Activities of other groups (group restrictions).
 *
 * @package    block_efprogress
 * @copyright  2026 Ana Rosiris Castro Mendez, eLearning Fácil (https://elearningfacil.com)
 * @author     Ana Rosiris Castro Mendez <info@elearningfacil.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_efprogress\local;

/**
 * An activity belongs to another group when its own access restriction, or
 * the one of its section, is a plain "all of" (&) tree at the root with a
 * group or grouping condition the student does not meet. Such an activity is
 * not part of the student's path: no padlock and it does not count.
 * Everything else behaves as always (dates, completion, complex trees).
 *
 * Same rule as local_efreport (copied on purpose: the block works without it).
 *
 * @package    block_efprogress
 * @copyright  2026 Ana Rosiris Castro Mendez, eLearning Fácil (https://elearningfacil.com)
 * @author     Ana Rosiris Castro Mendez <info@elearningfacil.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class groupaccess {
    /**
     * Whether the tree leaves the student out because of a group condition.
     *
     * @param string|null $json Availability JSON.
     * @param int[] $groupids Groups of the student in the course.
     * @param int[] $groupingids Groupings the student belongs to.
     * @return bool
     */
    public static function excluded_by_group(?string $json, array $groupids, array $groupingids): bool {
        if ($json === null || $json === '') {
            return false;
        }
        $tree = json_decode($json, true);
        if (!is_array($tree) || ($tree['op'] ?? '') !== '&' || empty($tree['c']) || !is_array($tree['c'])) {
            return false;
        }
        foreach ($tree['c'] as $c) {
            if (!is_array($c)) {
                continue;
            }
            $type = $c['type'] ?? '';
            if ($type === 'group') {
                if (!empty($c['id'])) {
                    if (!in_array((int) $c['id'], $groupids, true)) {
                        return true;
                    }
                } else if (!$groupids) {
                    // Any group: a student without a group can never reach it.
                    return true;
                }
            } else if ($type === 'grouping' && !empty($c['id'])) {
                if (!in_array((int) $c['id'], $groupingids, true)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Groups and groupings of a student in a course.
     *
     * @param int $courseid Course id.
     * @param int $userid Student id.
     * @return array [int[] groupids, int[] groupingids]
     */
    public static function user_groups(int $courseid, int $userid): array {
        $usergroups = groups_get_user_groups($courseid, $userid);
        $groupids = array_map('intval', $usergroups[0] ?? []);
        $groupingids = [];
        foreach ($usergroups as $groupingid => $ids) {
            if ((int) $groupingid !== 0 && !empty($ids)) {
                $groupingids[] = (int) $groupingid;
            }
        }
        return [$groupids, $groupingids];
    }

    /**
     * Whether the activity belongs to another group for this student.
     *
     * @param \cm_info $cm Activity.
     * @param array $usergroups Result of user_groups().
     * @return bool
     */
    public static function is_foreign(\cm_info $cm, array $usergroups): bool {
        [$groupids, $groupingids] = $usergroups;
        if (self::excluded_by_group($cm->availability, $groupids, $groupingids)) {
            return true;
        }
        $section = $cm->get_modinfo()->get_section_info_by_id($cm->section);
        return $section && self::excluded_by_group($section->availability, $groupids, $groupingids);
    }
}
