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

final class PayPalModeSwitchElement extends Element implements PayPalModeSwitchElementInterface
{
    private const SANDBOX_MODAL_SELECTOR = '#confirmation-modal-create-paypal-sandbox';

    public function setUpSandbox(string $clientId, string $clientSecret, string $merchantId): void
    {
        $this->selectMode('sandbox');

        $trigger = $this->getDocument()->waitFor(5, function () {
            $button = $this->getDocument()->find('css', sprintf('[data-bs-target="%s"]', self::SANDBOX_MODAL_SELECTOR));

            return ($button !== null && !str_contains((string) $button->getAttribute('class'), 'd-none')) ? $button : null;
        });
        if (null === $trigger) {
            throw new \RuntimeException('The "Set up sandbox" button never became visible after selecting sandbox mode.');
        }
        $trigger->click();

        $modal = $this->getDocument()->waitFor(5, fn () => $this->getDocument()->find('css', self::SANDBOX_MODAL_SELECTOR . '.show'));
        $modal->fillField('sylius_paypal_sandbox_credentials[clientId]', $clientId);
        $modal->fillField('sylius_paypal_sandbox_credentials[clientSecret]', $clientSecret);
        $modal->fillField('sylius_paypal_sandbox_credentials[merchantId]', $merchantId);
        $modal->find('css', 'button[form="paypal-sandbox-form"]')->press();

        $this->getDocument()->waitFor(5, fn () => str_contains($this->getSession()->getCurrentUrl(), '/edit'));
    }

    public function switchModeAndSave(string $mode): void
    {
        $this->selectMode($mode);
        $this->getDocument()->find('css', '[data-test-update-changes-button]')->click();
        $this->getDocument()->waitFor(5, fn () => str_contains($this->getSession()->getCurrentUrl(), '/edit'));
    }

    public function getDisplayedClientId(): string
    {
        return (string) $this->getDocument()->find('css', '[name$="[client_id]"]')->getValue();
    }

    private function selectMode(string $mode): void
    {
        $select = $this->getDocument()->find('css', '[data-paypal-mode-switch] select');
        $select->setValue($mode);

        $this->getSession()->executeScript(sprintf(
            "document.getElementById('%s').dispatchEvent(new Event('change', {bubbles: true}));",
            $select->getAttribute('id'),
        ));
    }
}
