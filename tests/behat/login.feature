@theme @theme_customboost
Feature: Login page with an image, a title and a tagline
  In order to recognise the site I am logging in to
  As a visitor
  I need to see the site title and tagline next to the login form

  Background:
    Given the following config values are set as admin:
      | theme | customboost |

  Scenario: The login page shows the site name and the default tagline
    When I visit "/login/index.php"
    Then I should see "Acceptance test site" in the ".ct-login-title" "css_element"
    And I should see "Learn at your own pace, from any device." in the ".ct-login-tagline" "css_element"
    And ".ct-login-image" "css_element" should exist
    And "Log in" "button" should exist

  Scenario: The administrator sets the title and the tagline, and markup in them is not rendered
    Given the following config values are set as admin:
      | logintitle   | Welcome to <b>R&D</b> campus | theme_customboost |
      | logintagline | Short & sweet                | theme_customboost |
    When I visit "/login/index.php"
    Then I should see "Welcome to R&D campus" in the ".ct-login-title" "css_element"
    And I should see "Short & sweet" in the ".ct-login-tagline" "css_element"
    And ".ct-login-title b" "css_element" should not exist

  Scenario: A user can log in from the themed login page
    Given the following "users" exist:
      | username | firstname | lastname | email           |
      | ana      | Ana       | García   | ana@example.com |
    When I visit "/login/index.php"
    And I set the field "Username" to "ana"
    And I set the field "Password" to "ana"
    And I press "Log in"
    Then I should see "Hello, Ana"
