### Legacy installation (without Symfony Flex)

1. Require plugin with composer:

    ```bash
    composer require sylius/paypal-plugin
    ```

1. Add plugin class and other required bundles to your `config/bundles.php`:

    ```php
    $bundles = [
        Sylius\PayPalPlugin\SyliusPayPalPlugin::class => ['all' => true],
    ];
    ```

1. Import configuration:

    ```yaml
    imports:
        - { resource: "@SyliusPayPalPlugin/Resources/config/config.yaml" }
    ```

1. Import routes:

    ```yaml
    # config/routes/sylius_shop.yaml

    sylius_paypal_shop:
        resource: "@SyliusPayPalPlugin/Resources/config/shop_routing.yaml"
        prefix: /{_locale}
        requirements:
            _locale: ^[A-Za-z]{2,4}(_([A-Za-z]{4}|[0-9]{3}))?(_([A-Za-z]{2}|[0-9]{3}))?$

    # config/routes/sylius_admin.yaml

    sylius_paypal_admin:
        resource: "@SyliusPayPalPlugin/Resources/config/admin_routing.yml"
        prefix: /admin

    # config/routes.yaml

    sylius_paypal_webhook:
        resource: "@SyliusPayPalPlugin/Resources/config/webhook_routing.yaml"
    ```

1. Override Sylius' templates

    ```bash
    cp -R vendor/sylius/paypal-plugin/src/Resources/views/bundles/* templates/bundles/
    ```

1. Apply migrations to your database:

    ```bash
    bin/console doctrine:migrations:migrate
    ```

1. Clear cache:

    ```bash
    bin/console cache:clear
    ```
