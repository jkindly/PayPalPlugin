@managing_payment_methods
Feature: Reopening the PayPal onboarding modal
    In order to retry connecting my PayPal account after changing my mind
    As an Administrator
    I want the "Connect with PayPal" modal to keep working after I close it without completing it

    Background:
        Given the store operates on a single channel in "United States"
        And I am logged in as an administrator
        And I am browsing payment methods

    @ui @javascript
    Scenario: Reopening the onboarding modal without completing it still offers a usable connect link
        When I open the "Connect with PayPal" onboarding modal
        And I close the onboarding modal without completing it and open it again
        Then only one partner.js script should have been loaded
