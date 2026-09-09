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

namespace Sylius\PayPalPlugin\Dispatcher;

use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\PayPalPlugin\Message\SendShipmentTracking;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class ShipmentTrackingDispatcher implements ShipmentTrackingDispatcherInterface
{
    public function __construct(private MessageBusInterface $messageBus)
    {
    }

    public function dispatch(ShipmentInterface $shipment): void
    {
        $shipmentId = $shipment->getId();
        if ($shipmentId === null) {
            return;
        }

        $this->messageBus->dispatch(new SendShipmentTracking($shipmentId));
    }
}
