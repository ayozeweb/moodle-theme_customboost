@theme @theme_customboost @javascript
Feature: Keyboard use on phones
  In order to use the site without a mouse or touch screen
  As a keyboard or switch user on a small screen
  I need the drawers, the skip link and the course cards to handle the focus well

  Background:
    Given the following config values are set as admin:
      | theme | customboost |
    And the following "users" exist:
      | username | firstname | lastname | email           |
      | ana      | Ana       | García   | ana@example.com |
    And the following "courses" exist:
      | fullname          | shortname |
      | Digital marketing | DM        |
      | Web development   | WD        |
    And the following "course enrolments" exist:
      | user | course | role    |
      | ana  | DM     | student |
      | ana  | WD     | student |

  Scenario: Escape closes the main menu and the focus goes back to its button
    Given I change viewport size to "360x800"
    And I log in as "ana"
    When I click on ".ct-navbar-toggler" "css_element"
    And "Dashboard" "link" in the "#theme_boost-drawers-primary" "css_element" should be visible
    And I press the escape key
    Then "Dashboard" "link" in the "#theme_boost-drawers-primary" "css_element" should not be visible
    And the focused element is ".ct-navbar-toggler" "css_element"

  Scenario: Tab stays inside the open main menu
    Given I change viewport size to "360x800"
    And I log in as "ana"
    When I click on ".ct-navbar-toggler" "css_element"
    And I press the tab key
    And I press the tab key
    And I press the tab key
    And I press the tab key
    And I press the tab key
    And I press the tab key
    Then the focus should be inside "#theme_boost-drawers-primary" in theme_customboost

  Scenario: The greeting and the course cards are part of the main content
    Given I log in as "ana"
    Then ".ct-dashboard-hero" "css_element" should exist in the "[role=main]" "css_element"
    And "#maincontent + .ct-dashboard-hero" "css_element" should exist
