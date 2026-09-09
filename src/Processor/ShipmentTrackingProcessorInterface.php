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

namespace Sylius\PayPalPlugin\Processor;

use Sylius\Component\Core\Model\ShipmentInterface;

interface ShipmentTrackingProcessorInterface
{
    /**
     * Sends the shipment's tracking information to PayPal.
     *
     * Sending tracking is a courtesy to PayPal, not a precondition for shipping goods: this method
     * never throws on a PayPal error. Failures are recorded on the shipment tracking record with
     * enough detail to be retried later, and the shipment stays shipped.
     */
    public function process(ShipmentInterface $shipment): void;
}
