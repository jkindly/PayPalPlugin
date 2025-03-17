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
        - { resource: '@SyliusPayPalPlugin/config/config.yaml' }
    ```

1. Import routes:

    ```yaml
    sylius_refund:
        resource: "@SyliusPayPalPlugin/config/routes.yaml"
    ```

1. Add `FOS\RestBundle` configuration to your `config/packages/fos_rest.yaml` file

    ```yaml
     fos_rest:
        exception: true
        view:
            formats:
                json: true
                xml:  true
            empty_content: 204
        format_listener:
            rules:
                - { path: '^/api/.*', priorities: ['json', 'xml'], fallback_format: json, prefer_extension: true }
                - { path: '^/', stop: true }
   ```

1. Apply migrations to your database:

    ```bash
    bin/console doctrine:migrations:migrate
    ```

1. Clear cache:

    ```bash
    bin/console cache:clear
    ```
