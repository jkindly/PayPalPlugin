@managing_payment_methods
Feature: Switching PayPal between sandbox and production without losing credentials
    In order to safely try sandbox credentials on a store that already takes real payments
    As an Administrator
    I want production credentials to survive setting up and switching to sandbox mode

    Background:
        Given the store operates on a single channel in "United States"
        And I am logged in as an administrator
        And the store allows paying with "PayPal" with "PayPal" factory name
        And I want to modify the "PayPal" payment method
        And I note the currently displayed PayPal client id as the production one

    @ui @javascript
    Scenario: Setting up sandbox credentials on an already configured production integration preserves the production client id
        When I set up PayPal sandbox with credentials "SANDBOX_ID", "SANDBOX_SECRET" and "SANDBOX_MERCHANT"
        And I switch the PayPal mode to "production"
        Then the PayPal client id should still be the production one

    @ui @javascript
    Scenario: Switching back to sandbox after returning to production does not lose the stored sandbox credentials
        When I set up PayPal sandbox with credentials "SANDBOX_ID", "SANDBOX_SECRET" and "SANDBOX_MERCHANT"
        And I switch the PayPal mode to "production"
        And I switch the PayPal mode to "sandbox"
        Then the PayPal client id should be "SANDBOX_ID"
