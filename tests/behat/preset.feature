@theme @theme_customboost
Feature: Colour presets
  In order to offer a light or a dark site
  As an administrator
  I need the colour preset to be applied to every page

  Background:
    Given the following config values are set as admin:
      | theme | customboost |

  Scenario: The light preset is the default
    When I visit "/login/index.php"
    Then "html[data-bs-theme='light']" "css_element" should exist

  Scenario: The dark preset applies to the login page and to logged in pages
    Given the following config values are set as admin:
      | colourpreset | dark | theme_customboost |
    When I visit "/login/index.php"
    Then "html[data-bs-theme='dark']" "css_element" should exist
    And I log in as "admin"
    And "html[data-bs-theme='dark']" "css_element" should exist

  Scenario: Boost colour modes take over when they are enabled
    Given the following config values are set as admin:
      | colourpreset      | dark | theme_customboost |
      | enablecolourmodes | 1    | theme_boost       |
    When I visit "/login/index.php"
    Then "html[data-ct-preset]" "css_element" should not exist
    And "html[data-bs-theme='light']" "css_element" should exist
