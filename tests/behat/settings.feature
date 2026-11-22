@theme @theme_customboost
Feature: Theme settings, live preview and tools
  In order to adapt the theme to my brand without breaking it
  As an administrator
  I need the settings to preview, validate, export and restore as documented

  Background:
    Given the following config values are set as admin:
      | theme | customboost |

  @javascript
  Scenario: The live preview follows the palette and the colour pickers before saving
    Given I log in as "admin"
    When I visit "/admin/settings.php?section=themesettingcustomboost"
    Then the theme preview should use "#4338ca" as the brand colour in theme_customboost
    And "#id_s_theme_customboost_brandcolour" "css_element" should not be visible
    And I set the field "Palette" to "Forest (green and amber)"
    And the theme preview should use "#147c3b" as the brand colour in theme_customboost
    And I set the field "Palette" to "Custom (brand and accent colours below)"
    And "#id_s_theme_customboost_brandcolour" "css_element" should be visible
    And I set the field "Brand colour" to "#ffcc00"
    And I should see "The chosen colours did not have enough contrast" in the "[data-region='ct-preview-status']" "css_element"
    And the theme preview should use "#806600" as the brand colour in theme_customboost

  Scenario: Saving the appearance settings
    Given I log in as "admin"
    And I visit "/admin/settings.php?section=themesettingcustomboost"
    When I set the following fields to these values:
      | Palette        | Ocean (blue and teal)       |
      | Typeface       | Device font (system-ui)     |
      | Text size      | 17 px                       |
      | Corner radius  | Square                      |
      | Density        | Compact                     |
    And I press "Save changes"
    Then I should see "Changes saved"
    And the following theme_customboost settings should be stored:
      | palette    | ocean   | theme_customboost |
      | fontfamily | system  | theme_customboost |
      | fontsize   | 17      | theme_customboost |
      | radius     | square  | theme_customboost |
      | density    | compact | theme_customboost |

  Scenario: Import, invalid import and restore from the tools page
    Given I log in as "admin"
    And I visit "/theme/customboost/tools.php"
    And I should see "reach their minimum"
    When I set the field "Or paste its content" to "{\"format\":\"theme_customboost-settings\",\"settings\":{\"palette\":\"garnet\",\"radius\":\"triangle\",\"nope\":\"1\"}}"
    And I press "Import settings"
    Then I should see "Import finished: 1 changed"
    And I should see "radius: value not accepted"
    And I should see "nope: unknown setting, skipped"
    And the following theme_customboost settings should be stored:
      | palette | garnet | theme_customboost |
    And I set the field "Or paste its content" to "{\"hello\": 1}"
    And I press "Import settings"
    And I should see "This is not a settings export of the Custom Boost theme."
    And I press "Restore defaults"
    And I press "Continue"
    And I should see "Defaults restored"
    And the following theme_customboost settings should be stored:
      | palette | indigo | theme_customboost |

  Scenario Outline: Login page layouts and the text under the form
    Given the following config values are set as admin:
      | loginlayout     | <layout>                             | theme_customboost |
      | loginfootertext | <p>Help desk: help@example.com</p>   | theme_customboost |
    When I visit "/login/index.php"
    Then ".ct-login-layout-<layout>" "css_element" should exist
    And ".ct-login-media" "css_element" <image> exist
    And I should see "Help desk: help@example.com" in the ".ct-login-footertext" "css_element"

    Examples:
      | layout | image      |
      | left   | should     |
      | right  | should     |
      | cover  | should     |
      | plain  | should not |

  Scenario: Footer text, site name and personal area options
    Given the following "users" exist:
      | username | firstname | lastname | email           |
      | ana      | Ana       | García   | ana@example.com |
    And the following "courses" exist:
      | fullname          | shortname | enddate    |
      | Digital marketing | DM        | 1924905600 |
      | Web development   | WD        | 0          |
    And the following "course enrolments" exist:
      | user | course | role    |
      | ana  | DM     | student |
      | ana  | WD     | student |
    And the following config values are set as admin:
      | footertext            | <p>Legal notice of the campus</p> | theme_customboost |
      | dashboardsource       | all                               | theme_customboost |
      | dashboardcardelements | enddate,continue                  | theme_customboost |
      | dashboardgreeting     | Welcome back, {firstname}!        | theme_customboost |
    When I log in as "ana"
    Then I should see "Welcome back, Ana!" in the ".ct-dashboard-hero" "css_element"
    And I should see "My courses" in the ".ct-dashboard-hero" "css_element"
    And I should see "Ends" in the ".ct-dashboard-hero" "css_element"
    And ".ct-course-card-continue" "css_element" should exist
    And ".ct-course-card-category" "css_element" should not exist
    And I should see "Legal notice of the campus" in the ".ct-site-footer" "css_element"
