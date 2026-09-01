@managing_payment_methods
Feature: Enabling PayPal via sandbox credentials
    In order to quickly test the PayPal integration without a full onboarding
    As an Administrator
    I want to enable PayPal by providing sandbox credentials directly

    Background:
        Given the store operates on a single channel in "United States"
        And I am logged in as an administrator

    @ui @javascript
    Scenario: Enabling PayPal using sandbox credentials
        When I am browsing payment methods
        And I enable PayPal using sandbox credentials "CLIENT_ID", "CLIENT_SECRET" and "MERCHANT_ID"
        Then the payment method "PayPal" should appear in the registry
        And I want to modify the "PayPal" payment method
        And this payment method should be enabled
