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

interface PayPalModeSwitchElementInterface
{
    public function setUpSandbox(string $clientId, string $clientSecret, string $merchantId): void;

    public function switchModeAndSave(string $mode): void;

    public function getDisplayedClientId(): string;
}
