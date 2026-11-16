@theme @theme_customboost @javascript
Feature: Compact header on phones
  In order to navigate the site with one hand
  As a user on a phone
  I need the brand to stay visible and the main menu to open in a drawer

  Background:
    Given the following config values are set as admin:
      | theme | customboost |

  Scenario: The brand is visible and the menu opens and closes in a drawer at 360 px
    Given I change viewport size to "360x800"
    When I log in as "admin"
    Then ".ct-brand" "css_element" should be visible
    And ".primary-navigation" "css_element" should not be visible
    And I click on ".ct-navbar-toggler" "css_element"
    And "Dashboard" "link" in the "#theme_boost-drawers-primary" "css_element" should be visible
    And I click on "Close drawer" "button" in the "#theme_boost-drawers-primary" "css_element"
    And "Dashboard" "link" in the "#theme_boost-drawers-primary" "css_element" should not be visible

  Scenario: The page does not scroll sideways at 360 px
    Given I change viewport size to "360x800"
    When I log in as "admin"
    Then the page should not scroll horizontally in theme_customboost
    And I visit "/my/courses.php"
    And the page should not scroll horizontally in theme_customboost
