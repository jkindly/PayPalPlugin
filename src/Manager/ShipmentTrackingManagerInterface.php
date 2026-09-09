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

namespace Sylius\PayPalPlugin\Manager;

use Sylius\Component\Core\Model\ShipmentInterface;

interface ShipmentTrackingManagerInterface
{
    /**
     * Stores the carrier selected for a shipment, creating the tracking record if needed. The
     * record is flushed so it is readable both by a synchronous handler (still inside the request)
     * and by an asynchronous worker processing the message later.
     */
    public function updateCarrier(ShipmentInterface $shipment, ?string $carrier, ?string $carrierNameOther): void;
}
