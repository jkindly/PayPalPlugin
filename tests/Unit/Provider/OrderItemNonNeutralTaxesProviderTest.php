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

namespace Tests\Sylius\PayPalPlugin\Unit\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\OrderItemUnitInterface;
use Sylius\PayPalPlugin\Provider\OrderItemNonNeutralTaxesProvider;

final class OrderItemNonNeutralTaxesProviderTest extends TestCase
{
    private OrderItemNonNeutralTaxesProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = new OrderItemNonNeutralTaxesProvider();
    }

    public function testProvidesNonNeutralTaxBasedOnGivenOrderItem(): void
    {
        $orderItem = $this->createMock(OrderItemInterface::class);
        $adjustment = $this->createMock(AdjustmentInterface::class);
        $orderItemUnit = $this->createMock(OrderItemUnitInterface::class);
        $unitAdjustment = $this->createMock(AdjustmentInterface::class);

        $orderItem->method('getAdjustments')
            ->with(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([$adjustment]));

        $adjustment->method('isNeutral')->willReturn(true);
        $adjustment->expects($this->never())->method('getAmount');

        $orderItem->method('getUnits')->willReturn(new ArrayCollection([$orderItemUnit]));
        $orderItemUnit->method('getAdjustments')
            ->with(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([$unitAdjustment]));

        $unitAdjustment->method('isNeutral')->willReturn(false);
        $unitAdjustment->method('getAmount')->willReturn(20);

        $result = $this->provider->provide($orderItem);

        self::assertEquals([20], $result);
    }
}
