@theme @theme_customboost @javascript @accessibility
Feature: The theme's own pages meet WCAG 2.1 AA in both colour presets
  In order to use the site with assistive technology
  As any user
  I need the login page, the personal area and the theme settings to pass the automated accessibility checks

  Background:
    Given the following config values are set as admin:
      | theme | customboost |
    And the following "users" exist:
      | username | firstname | lastname | email           |
      | ana      | Ana       | García   | ana@example.com |
    And the following "courses" exist:
      | fullname          | shortname | enablecompletion |
      | Digital marketing | DM        | 1                |
    And the following "course enrolments" exist:
      | user | course | role    |
      | ana  | DM     | student |

  Scenario Outline: Login page, personal area and course page in the <preset> preset
    Given the following config values are set as admin:
      | colourpreset | <preset> | theme_customboost |
    When I visit "/login/index.php"
    Then the page should meet accessibility standards with "wcag143" extra tests
    And I log in as "ana"
    And the page should meet accessibility standards with "wcag143" extra tests
    And I am on "Digital marketing" course homepage
    And the page should meet accessibility standards with "wcag143" extra tests

    Examples:
      | preset |
      | light  |
      | dark   |

  Scenario Outline: Theme settings page in the <preset> preset
    Given the following config values are set as admin:
      | colourpreset | <preset> | theme_customboost |
    When I log in as "admin"
    And I visit "/admin/settings.php?section=themesettingcustomboost"
    Then the page should meet accessibility standards
    And I click on "Login page" "link" in the "[role=tablist]" "css_element"
    And the page should meet accessibility standards

    Examples:
      | preset |
      | light  |
      | dark   |
