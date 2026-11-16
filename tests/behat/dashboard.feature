@theme @theme_customboost
Feature: Course cards on the personal area
  In order to continue learning quickly
  As a student
  I need to see my courses and my progress at the top of my personal area

  Background:
    Given the following config values are set as admin:
      | theme | customboost |
    And the following "users" exist:
      | username | firstname | lastname | email             |
      | ana      | Ana       | García   | ana@example.com   |
      | bruno    | Bruno     | Díaz     | bruno@example.com |
    And the following "categories" exist:
      | name      | category | idnumber |
      | Marketing | 0        | MKT      |
    And the following "courses" exist:
      | fullname          | shortname | category | enablecompletion |
      | Digital marketing | DM        | MKT      | 1                |
      | Web development   | WD        | 0        | 0                |
    And the following "course enrolments" exist:
      | user | course | role    |
      | ana  | DM     | student |
      | ana  | WD     | student |
    And the following "activities" exist:
      | activity | course | name     | completion |
      | page     | DM     | Lesson 1 | 1          |

  Scenario: A student sees a greeting and a card for each course
    When I log in as "ana"
    Then I should see "Hello, Ana" in the ".ct-dashboard-hero" "css_element"
    And I should see "Continue learning" in the ".ct-dashboard-hero" "css_element"
    And I should see "Digital marketing" in the ".ct-course-grid" "css_element"
    And I should see "Web development" in the ".ct-course-grid" "css_element"
    And I should see "Marketing" in the ".ct-course-grid" "css_element"
    And I should see "Not started" in the ".ct-course-grid" "css_element"

  Scenario: A card links to its course
    When I log in as "ana"
    And I click on "Digital marketing" "link" in the ".ct-course-grid" "css_element"
    Then I should see "Lesson 1"

  Scenario: A user without courses sees an empty state
    When I log in as "bruno"
    Then I should see "Hello, Bruno" in the ".ct-dashboard-hero" "css_element"
    And I should see "You are not enrolled in any course yet."
    And ".ct-course-grid" "css_element" should not exist

  Scenario: The administrator can turn the course cards off
    Given the following config values are set as admin:
      | dashboardcourses | 0 | theme_customboost |
    When I log in as "ana"
    Then ".ct-dashboard-hero" "css_element" should not exist

  Scenario: The link to all courses is only shown when My courses is enabled
    Given the following config values are set as admin:
      | enablemycourses | 0 |
    When I log in as "ana"
    Then "All my courses" "link" should not exist
    And the following config values are set as admin:
      | enablemycourses | 1 |
    And I reload the page
    And "All my courses" "link" should exist
