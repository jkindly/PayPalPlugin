<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\Sylius\PayPalPlugin\Behat\Element;

use FriendsOfBehat\PageObjectExtension\Element\Element;

final class EnablePayPalSandboxElement extends Element implements EnablePayPalSandboxElementInterface
{
    private const MODAL_SELECTOR = '#confirmation-modal-create-paypal-sandbox';

    public function enableWithCredentials(string $clientId, string $clientSecret, string $merchantId): void
    {
        $this->getDocument()->find('css', sprintf('[data-bs-target="%s"]', self::MODAL_SELECTOR))->click();

        $modal = $this->getDocument()->waitFor(5, fn () => $this->getDocument()->find('css', self::MODAL_SELECTOR . '.show'));

        $modal->fillField('sylius_paypal_sandbox_credentials[clientId]', $clientId);
        $modal->fillField('sylius_paypal_sandbox_credentials[clientSecret]', $clientSecret);
        $modal->fillField('sylius_paypal_sandbox_credentials[merchantId]', $merchantId);

        $modal->find('css', 'button[form="paypal-sandbox-form"]')->press();

        $this->getDocument()->waitFor(5, fn () => str_contains($this->getSession()->getCurrentUrl(), '/edit'));
    }
}
