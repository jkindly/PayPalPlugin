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
use Tests\Sylius\PayPalPlugin\Behat\Element\PayPalModeSwitchElementInterface;
use Webmozart\Assert\Assert;

final class ManagingPayPalModeContext implements Context
{
    private ?string $productionClientId = null;

    public function __construct(private readonly PayPalModeSwitchElementInterface $modeSwitchElement)
    {
    }

    /**
     * @When I note the currently displayed PayPal client id as the production one
     */
    public function iNoteTheCurrentClientIdAsProduction(): void
    {
        $this->productionClientId = $this->modeSwitchElement->getDisplayedClientId();
    }

    /**
     * @When I set up PayPal sandbox with credentials :clientId, :clientSecret and :merchantId
     */
    public function iSetUpPayPalSandboxWithCredentials(string $clientId, string $clientSecret, string $merchantId): void
    {
        $this->modeSwitchElement->setUpSandbox($clientId, $clientSecret, $merchantId);
    }

    /**
     * @When I switch the PayPal mode to :mode
     */
    public function iSwitchThePayPalModeTo(string $mode): void
    {
        $this->modeSwitchElement->switchModeAndSave($mode);
    }

    /**
     * @Then the PayPal client id should still be the production one
     */
    public function thePayPalClientIdShouldStillBeTheProductionOne(): void
    {
        Assert::notNull($this->productionClientId, 'The production client id was never noted.');
        Assert::same($this->productionClientId, $this->modeSwitchElement->getDisplayedClientId());
    }

    /**
     * @Then the PayPal client id should be :clientId
     */
    public function thePayPalClientIdShouldBe(string $clientId): void
    {
        Assert::same($clientId, $this->modeSwitchElement->getDisplayedClientId());
    }
}
