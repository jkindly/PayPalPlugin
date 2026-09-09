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

use Sylius\Component\Core\Model\ShipmentInterface;

interface ShipmentTrackingItemsProviderInterface
{
    /**
     * Builds the PayPal "items" payload from a single shipment's own units, so that an order split
     * across several shipments produces one tracker per parcel, each carrying only its items.
     *
     * @return list<array<string, mixed>>
     */
    public function provide(ShipmentInterface $shipment): array;
}
