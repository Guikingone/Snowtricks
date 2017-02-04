Feature: homepage
  In order to access the whole website
  a user must be anonymous (in the first time)
  and find the index page
  Scenario: Search for the hello world phrase
    Given: I'm on "/"
    Then the response status code should be 200
