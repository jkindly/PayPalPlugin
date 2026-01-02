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
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function provides_non_neutral_tax_based_on_given_order_item(): void
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

    #[Test]
    public function returns_empty_array_when_no_taxes_exist(): void
    {
        $orderItem = $this->createMock(OrderItemInterface::class);
        $orderItemUnit = $this->createMock(OrderItemUnitInterface::class);

        $orderItem->method('getAdjustments')
            ->with(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([]));

        $orderItem->method('getUnits')->willReturn(new ArrayCollection([$orderItemUnit]));
        $orderItemUnit->method('getAdjustments')
            ->with(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([]));

        $result = $this->provider->provide($orderItem);

        self::assertEquals([], $result);
    }

    #[Test]
    public function returns_empty_array_when_only_neutral_taxes_exist(): void
    {
        $orderItem = $this->createMock(OrderItemInterface::class);
        $neutralAdjustment1 = $this->createMock(AdjustmentInterface::class);
        $neutralAdjustment2 = $this->createMock(AdjustmentInterface::class);
        $orderItemUnit = $this->createMock(OrderItemUnitInterface::class);
        $neutralUnitAdjustment = $this->createMock(AdjustmentInterface::class);

        $orderItem->method('getAdjustments')
            ->with(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([
                $neutralAdjustment1,
                $neutralAdjustment2,
            ]));

        $neutralAdjustment1->method('isNeutral')->willReturn(true);
        $neutralAdjustment1->expects($this->never())->method('getAmount');
        $neutralAdjustment2->method('isNeutral')->willReturn(true);
        $neutralAdjustment2->expects($this->never())->method('getAmount');

        $orderItem->method('getUnits')->willReturn(new ArrayCollection([$orderItemUnit]));
        $orderItemUnit->method('getAdjustments')
            ->with(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([$neutralUnitAdjustment]));

        $neutralUnitAdjustment->method('isNeutral')->willReturn(true);
        $neutralUnitAdjustment->expects($this->never())->method('getAmount');

        $result = $this->provider->provide($orderItem);

        self::assertEquals([], $result);
    }

    #[Test]
    public function collects_multiple_non_neutral_taxes_from_order_item_and_units(): void
    {
        $orderItem = $this->createMock(OrderItemInterface::class);
        $orderItemTax1 = $this->createMock(AdjustmentInterface::class);
        $orderItemTax2 = $this->createMock(AdjustmentInterface::class);
        $orderItemUnit1 = $this->createMock(OrderItemUnitInterface::class);
        $orderItemUnit2 = $this->createMock(OrderItemUnitInterface::class);
        $unit1Tax = $this->createMock(AdjustmentInterface::class);
        $unit2Tax = $this->createMock(AdjustmentInterface::class);

        $orderItem->method('getAdjustments')
            ->with(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([
                $orderItemTax1,
                $orderItemTax2,
            ]));

        $orderItemTax1->method('isNeutral')->willReturn(false);
        $orderItemTax1->method('getAmount')->willReturn(100);
        $orderItemTax2->method('isNeutral')->willReturn(false);
        $orderItemTax2->method('getAmount')->willReturn(50);

        $orderItem->method('getUnits')->willReturn(new ArrayCollection([
            $orderItemUnit1,
            $orderItemUnit2,
        ]));

        $orderItemUnit1->method('getAdjustments')
            ->with(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([$unit1Tax]));
        $orderItemUnit2->method('getAdjustments')
            ->with(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([$unit2Tax]));

        $unit1Tax->method('isNeutral')->willReturn(false);
        $unit1Tax->method('getAmount')->willReturn(25);
        $unit2Tax->method('isNeutral')->willReturn(false);
        $unit2Tax->method('getAmount')->willReturn(25);

        $result = $this->provider->provide($orderItem);

        self::assertEquals([100, 50, 25, 25], $result);
    }

    #[Test]
    public function filters_out_neutral_taxes_and_only_returns_non_neutral(): void
    {
        $orderItem = $this->createMock(OrderItemInterface::class);
        $nonNeutralTax = $this->createMock(AdjustmentInterface::class);
        $neutralTax = $this->createMock(AdjustmentInterface::class);
        $orderItemUnit = $this->createMock(OrderItemUnitInterface::class);
        $unitNonNeutralTax = $this->createMock(AdjustmentInterface::class);
        $unitNeutralTax = $this->createMock(AdjustmentInterface::class);

        $orderItem->method('getAdjustments')
            ->with(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([
                $nonNeutralTax,
                $neutralTax,
            ]));

        $nonNeutralTax->method('isNeutral')->willReturn(false);
        $nonNeutralTax->method('getAmount')->willReturn(200);
        $neutralTax->method('isNeutral')->willReturn(true);
        $neutralTax->expects($this->never())->method('getAmount');

        $orderItem->method('getUnits')->willReturn(new ArrayCollection([$orderItemUnit]));
        $orderItemUnit->method('getAdjustments')
            ->with(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([
                $unitNonNeutralTax,
                $unitNeutralTax,
            ]));

        $unitNonNeutralTax->method('isNeutral')->willReturn(false);
        $unitNonNeutralTax->method('getAmount')->willReturn(30);
        $unitNeutralTax->method('isNeutral')->willReturn(true);
        $unitNeutralTax->expects($this->never())->method('getAmount');

        $result = $this->provider->provide($orderItem);

        self::assertEquals([200, 30], $result);
    }

    #[Test]
    public function handles_multiple_units_with_different_tax_amounts(): void
    {
        $orderItem = $this->createMock(OrderItemInterface::class);
        $unit1 = $this->createMock(OrderItemUnitInterface::class);
        $unit2 = $this->createMock(OrderItemUnitInterface::class);
        $unit3 = $this->createMock(OrderItemUnitInterface::class);
        $unit1Tax = $this->createMock(AdjustmentInterface::class);
        $unit2Tax = $this->createMock(AdjustmentInterface::class);
        $unit3Tax = $this->createMock(AdjustmentInterface::class);

        $orderItem->method('getAdjustments')
            ->with(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([]));

        $orderItem->method('getUnits')->willReturn(new ArrayCollection([
            $unit1,
            $unit2,
            $unit3,
        ]));

        $unit1->method('getAdjustments')
            ->with(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([$unit1Tax]));
        $unit2->method('getAdjustments')
            ->with(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([$unit2Tax]));
        $unit3->method('getAdjustments')
            ->with(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([$unit3Tax]));

        $unit1Tax->method('isNeutral')->willReturn(false);
        $unit1Tax->method('getAmount')->willReturn(100);
        $unit2Tax->method('isNeutral')->willReturn(false);
        $unit2Tax->method('getAmount')->willReturn(100);
        $unit3Tax->method('isNeutral')->willReturn(false);
        $unit3Tax->method('getAmount')->willReturn(100);

        $result = $this->provider->provide($orderItem);

        self::assertEquals([100, 100, 100], $result);
    }
}
