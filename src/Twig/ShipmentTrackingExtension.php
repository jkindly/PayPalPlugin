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

namespace Sylius\PayPalPlugin\Twig;

use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\PayPalPlugin\Entity\ShipmentTrackingInterface;
use Sylius\PayPalPlugin\Repository\ShipmentTrackingRepositoryInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ShipmentTrackingExtension extends AbstractExtension
{
    public function __construct(private readonly ShipmentTrackingRepositoryInterface $shipmentTrackingRepository)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sylius_paypal_shipment_tracking', $this->getShipmentTracking(...)),
        ];
    }

    public function getShipmentTracking(ShipmentInterface $shipment): ?ShipmentTrackingInterface
    {
        return $this->shipmentTrackingRepository->findOneByShipment($shipment);
    }
}
