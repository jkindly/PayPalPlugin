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

namespace Sylius\PayPalPlugin\Message;

/**
 * Dispatched when a shipment is shipped, so the PayPal tracking call runs outside the ship
 * transition's transactional boundary. Route this message to an async transport for full
 * isolation; with no routing configured it is handled synchronously (still isolated from the
 * transition by the handler catching every error).
 */
final class SendShipmentTracking
{
    public function __construct(public readonly int|string $shipmentId)
    {
    }
}
