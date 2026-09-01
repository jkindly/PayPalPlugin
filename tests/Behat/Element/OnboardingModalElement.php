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

final class OnboardingModalElement extends Element implements OnboardingModalElementInterface
{
    private const MODAL_ID = 'confirmation-modal-create-pay-pal';

    /**
     * Triggers the same "paypal:onboarding-open" custom event that a real click on the grid's
     * "Connect with PayPal" trigger dispatches (see assets/admin/scripts/paypal-onboarding.js),
     * so it reproduces exactly what a close-without-completing-then-reopen cycle does to the
     * modal's live-component-rendered content - regardless of whether that trigger is currently
     * reachable through the grid dropdown (it is gated by the sandbox/production flag there).
     */
    public function open(): string
    {
        $previousHref = $this->getCurrentConnectLinkHref();

        $this->getSession()->executeScript(sprintf(
            "document.getElementById('%s').dispatchEvent(new Event('paypal:onboarding-open'));",
            self::MODAL_ID,
        ));

        $link = $this->getDocument()->waitFor(
            10,
            fn () => $this->findConnectLink() !== null
                && $this->findConnectLink()->getAttribute('href') !== $previousHref
                ? $this->findConnectLink()
                : null,
        );

        if (null === $link) {
            throw new \RuntimeException('The onboarding connect link never appeared (or never refreshed) after opening the modal.');
        }

        return (string) $link->getAttribute('href');
    }

    public function getLoadedPartnerScriptCount(): int
    {
        return (int) $this->getSession()->evaluateScript(
            "return document.querySelectorAll('script[data-paypal-partner-js]').length;",
        );
    }

    private function getCurrentConnectLinkHref(): ?string
    {
        return $this->findConnectLink()?->getAttribute('href');
    }

    private function findConnectLink(): ?\Behat\Mink\Element\NodeElement
    {
        return $this->getDocument()->find('css', sprintf('#%s [data-paypal-button]', self::MODAL_ID));
    }
}
