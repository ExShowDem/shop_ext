Feature: administrator login
  In order to manage my web application
  As administrator
  I need to be able to login

  Scenario: Successful login
    Given I am a registered joomla administrator
    When I login into Joomla Administrator
    Then I should see administrator dashboard