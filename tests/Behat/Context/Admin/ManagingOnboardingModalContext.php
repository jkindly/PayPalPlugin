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

namespace Tests\Sylius\PayPalPlugin\Behat\Context\Admin;

use Behat\Behat\Context\Context;
use Tests\Sylius\PayPalPlugin\Behat\Element\OnboardingModalElementInterface;
use Webmozart\Assert\Assert;

final class ManagingOnboardingModalContext implements Context
{
    public function __construct(private readonly OnboardingModalElementInterface $onboardingModalElement)
    {
    }

    /**
     * @When I open the "Connect with PayPal" onboarding modal
     */
    public function iOpenTheOnboardingModal(): void
    {
        $this->onboardingModalElement->open();
    }

    /**
     * @When I close the onboarding modal without completing it and open it again
     */
    public function iReopenTheOnboardingModalWithoutCompletingIt(): void
    {
        $this->onboardingModalElement->open();
    }

    /**
     * @Then only one partner.js script should have been loaded
     */
    public function onlyOnePartnerScriptShouldHaveBeenLoaded(): void
    {
        Assert::same(1, $this->onboardingModalElement->getLoadedPartnerScriptCount());
    }
}
