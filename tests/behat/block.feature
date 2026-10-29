@block @block_efprogress
Feature: The student progress block shows its rings in the course
  In order to know how I am doing at a glance
  As a student
  I need the progress block visible in my course

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | enablecompletion |
      | Course 1 | C1        | 0        | 1                |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Ana       | Alumna   | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
    And the following "activities" exist:
      | activity | course | name      | idnumber | completion |
      | page     | C1     | Content 1 | page1    | 1          |
    And the following "blocks" exist:
      | blockname  | contextlevel | reference | pagetypepattern | defaultregion |
      | efprogress | Course       | C1        | course-view-*   | side-pre      |

  Scenario: The student sees the block with the activities ring
    When I am on the "Course 1" course page logged in as student1
    Then I should see "My Progress"
    And I should see "Activities"

  Scenario: Restricted activities appear as locked marks with the reason
    Given the following "activities" exist:
      | activity | course | name       | idnumber | completion | availability                                                            |
      | page     | C1     | Later page | page2    | 1          | {"op":"&","c":[{"type":"date","d":">=","t":4102444800}],"showc":[true]} |
    When I am on the "Course 1" course page logged in as student1
    Then I should see "🔒" in the "My Progress" "block"
    And I should see "1 locked" in the "My Progress" "block"
