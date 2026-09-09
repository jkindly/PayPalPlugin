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

namespace Sylius\PayPalPlugin\Provider;

interface CarrierProviderInterface
{
    public const OTHER_CARRIER_CODE = 'OTHER';

    /**
     * @return array<string, string> map of PayPal carrier code => human-readable label
     */
    public function getCarriers(): array;

    public function isKnownCarrier(string $code): bool;

    public function isOther(string $code): bool;
}
