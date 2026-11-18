@theme @theme_customboost
Feature: Administering the theme and using it with Moodle's own features
  In order to adapt the theme to my site without breaking it
  As an administrator
  I need the settings, the login flow and the colour modes to behave as documented

  Background:
    Given the following config values are set as admin:
      | theme | customboost |

  Scenario: A wrong password shows the error inside the login sheet
    When I visit "/login/index.php"
    And I set the field "Username" to "admin"
    And I set the field "Password" to "wrong password"
    And I press "Log in"
    Then I should see "Unable to log in" in the ".ct-login-sheet" "css_element"
    And "#loginerrormessage[role='alert']" "css_element" should exist

  Scenario: Multi-language and long login texts
    Given the "multilang" filter is "on"
    And the "multilang" filter applies to "content and headings"
    And the following config values are set as admin:
      | logintitle   | <span lang="en" class="multilang">Welcome</span><span lang="es" class="multilang">Bienvenida</span> | theme_customboost |
      | logintagline | Learning that fits around your life: short lessons, clear goals and help whenever you need it. Study on the bus, at lunch or at home, and pick up exactly where you left off on any device. | theme_customboost |
    When I visit "/login/index.php"
    Then I should see "Welcome" in the ".ct-login-title" "css_element"
    And I should not see "Bienvenida"
    And I should see "pick up exactly where you left off on any device." in the ".ct-login-tagline" "css_element"

  @javascript @_file_upload
  Scenario: The administrator uploads a login image
    Given I log in as "admin"
    And I visit "/admin/settings.php?section=themesettingcustomboost"
    And I click on "Login page" "link" in the "[role='tablist']" "css_element"
    And I upload "theme/customboost/tests/fixtures/images/course2.jpg" file to "Login image" filemanager
    And I press "Save changes"
    And I log out
    When I visit "/login/index.php"
    Then "img.ct-login-image[src*='loginimage']" "css_element" should exist

  Scenario: Invalid SCSS is rejected when saving
    Given I log in as "admin"
    And I visit "/admin/settings.php?section=themesettingcustomboost"
    When I set the field "Raw SCSS" to "body { color: ;"
    And I press "Save changes"
    Then I should see "SCSS code is not valid"

  @javascript
  Scenario: The course cards stay out of the way in editing mode
    Given the following "users" exist:
      | username | firstname | lastname | email           |
      | ana      | Ana       | García   | ana@example.com |
    And the following "courses" exist:
      | fullname          | shortname |
      | Digital marketing | DM        |
    And the following "course enrolments" exist:
      | user | course | role    |
      | ana  | DM     | student |
    When I log in as "ana"
    And I turn editing mode on
    Then I should see "Digital marketing" in the ".ct-dashboard-hero" "css_element"
    And I add the "Online users" block
    And I should see "Online users" in the "Online users" "block"

  @javascript
  Scenario: With Boost colour modes on, each user picks their own mode
    Given the following config values are set as admin:
      | enablecolourmodes | 1    | theme_boost       |
      | colourpreset      | dark | theme_customboost |
    When I log in as "admin"
    Then "html[data-bs-theme='light']" "css_element" should exist
    And I click on "#colourmode-menu-toggle" "css_element"
    And I click on "[data-colourmode='dark']" "css_element"
    And "html[data-bs-theme='dark']" "css_element" should exist
    And I reload the page
    And "html[data-bs-theme='dark']" "css_element" should exist
