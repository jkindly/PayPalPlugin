## Installation

#### BEWARE!

This installation instruction assumes that you're using Symfony Flex. If you don't, take a look at the
[legacy installation instruction](legacy_installation.md). However, we strongly encourage you to use Symfony Flex.

1. Require plugin with composer:

    ```bash
    composer require sylius/paypal-plugin
    ```

   > Remember to allow community recipes with `composer config extra.symfony.allow-contrib true` or during plugin installation process

1. Apply migrations:

   ```
   bin/console doctrine:migrations:migrate -n
   ```

1. Clear cache:

    ```bash
    bin/console cache:clear
    ```

To make PayPal integration working, your local Sylius URL should be accessible for the PayPal servers. Therefore, you can
add the proper directive to your `/etc/hosts` (something like `127.0.0.1 sylius.local`) or use a service as [ngrok](https://ngrok.com/).

---

Next: [PayPal environment](sandbox-vs-live.md)
