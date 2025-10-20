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

namespace spec\Sylius\PayPalPlugin\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\OrderItemUnitInterface;

final class OrderItemNonNeutralTaxesProviderSpec extends ObjectBehavior
{
    function it_provides_non_neutral_tax_based_on_given_order_item(
        OrderItemInterface $orderItem,
        AdjustmentInterface $adjustment,
        OrderItemUnitInterface $orderItemUnit,
        AdjustmentInterface $unitAdjustment,
    ): void {
        $orderItem->getAdjustments(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([$adjustment->getWrappedObject()]));

        $adjustment->isNeutral()->willReturn(true);
        $adjustment->getAmount()->shouldNotBeCalled();

        $orderItem->getUnits()->willReturn(new ArrayCollection([$orderItemUnit->getWrappedObject()]));
        $orderItemUnit->getAdjustments(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([$unitAdjustment->getWrappedObject()]));

        $unitAdjustment->isNeutral()->willReturn(false);
        $unitAdjustment->getAmount()->willReturn(20);

        $this->provide($orderItem)->shouldReturn([20]);
    }

    function it_returns_empty_array_when_no_taxes_exist(
        OrderItemInterface $orderItem,
        OrderItemUnitInterface $orderItemUnit,
    ): void {
        $orderItem->getAdjustments(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([]));

        $orderItem->getUnits()->willReturn(new ArrayCollection([$orderItemUnit->getWrappedObject()]));
        $orderItemUnit->getAdjustments(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([]));

        $this->provide($orderItem)->shouldReturn([]);
    }

    function it_returns_empty_array_when_only_neutral_taxes_exist(
        OrderItemInterface $orderItem,
        AdjustmentInterface $neutralAdjustment1,
        AdjustmentInterface $neutralAdjustment2,
        OrderItemUnitInterface $orderItemUnit,
        AdjustmentInterface $neutralUnitAdjustment,
    ): void {
        $orderItem->getAdjustments(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([
                $neutralAdjustment1->getWrappedObject(),
                $neutralAdjustment2->getWrappedObject(),
            ]));

        $neutralAdjustment1->isNeutral()->willReturn(true);
        $neutralAdjustment1->getAmount()->shouldNotBeCalled();
        $neutralAdjustment2->isNeutral()->willReturn(true);
        $neutralAdjustment2->getAmount()->shouldNotBeCalled();

        $orderItem->getUnits()->willReturn(new ArrayCollection([$orderItemUnit->getWrappedObject()]));
        $orderItemUnit->getAdjustments(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([$neutralUnitAdjustment->getWrappedObject()]));

        $neutralUnitAdjustment->isNeutral()->willReturn(true);
        $neutralUnitAdjustment->getAmount()->shouldNotBeCalled();

        $this->provide($orderItem)->shouldReturn([]);
    }

    function it_collects_multiple_non_neutral_taxes_from_order_item_and_units(
        OrderItemInterface $orderItem,
        AdjustmentInterface $orderItemTax1,
        AdjustmentInterface $orderItemTax2,
        OrderItemUnitInterface $orderItemUnit1,
        OrderItemUnitInterface $orderItemUnit2,
        AdjustmentInterface $unit1Tax,
        AdjustmentInterface $unit2Tax,
    ): void {
        $orderItem->getAdjustments(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([
                $orderItemTax1->getWrappedObject(),
                $orderItemTax2->getWrappedObject(),
            ]));

        $orderItemTax1->isNeutral()->willReturn(false);
        $orderItemTax1->getAmount()->willReturn(100);
        $orderItemTax2->isNeutral()->willReturn(false);
        $orderItemTax2->getAmount()->willReturn(50);

        $orderItem->getUnits()->willReturn(new ArrayCollection([
            $orderItemUnit1->getWrappedObject(),
            $orderItemUnit2->getWrappedObject(),
        ]));

        $orderItemUnit1->getAdjustments(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([$unit1Tax->getWrappedObject()]));
        $orderItemUnit2->getAdjustments(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([$unit2Tax->getWrappedObject()]));

        $unit1Tax->isNeutral()->willReturn(false);
        $unit1Tax->getAmount()->willReturn(25);
        $unit2Tax->isNeutral()->willReturn(false);
        $unit2Tax->getAmount()->willReturn(25);

        $this->provide($orderItem)->shouldReturn([100, 50, 25, 25]);
    }

    function it_filters_out_neutral_taxes_and_only_returns_non_neutral(
        OrderItemInterface $orderItem,
        AdjustmentInterface $nonNeutralTax,
        AdjustmentInterface $neutralTax,
        OrderItemUnitInterface $orderItemUnit,
        AdjustmentInterface $unitNonNeutralTax,
        AdjustmentInterface $unitNeutralTax,
    ): void {
        $orderItem->getAdjustments(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([
                $nonNeutralTax->getWrappedObject(),
                $neutralTax->getWrappedObject(),
            ]));

        $nonNeutralTax->isNeutral()->willReturn(false);
        $nonNeutralTax->getAmount()->willReturn(200);
        $neutralTax->isNeutral()->willReturn(true);
        $neutralTax->getAmount()->shouldNotBeCalled();

        $orderItem->getUnits()->willReturn(new ArrayCollection([$orderItemUnit->getWrappedObject()]));
        $orderItemUnit->getAdjustments(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([
                $unitNonNeutralTax->getWrappedObject(),
                $unitNeutralTax->getWrappedObject(),
            ]));

        $unitNonNeutralTax->isNeutral()->willReturn(false);
        $unitNonNeutralTax->getAmount()->willReturn(30);
        $unitNeutralTax->isNeutral()->willReturn(true);
        $unitNeutralTax->getAmount()->shouldNotBeCalled();

        $this->provide($orderItem)->shouldReturn([200, 30]);
    }

    function it_handles_multiple_units_with_different_tax_amounts(
        OrderItemInterface $orderItem,
        OrderItemUnitInterface $unit1,
        OrderItemUnitInterface $unit2,
        OrderItemUnitInterface $unit3,
        AdjustmentInterface $unit1Tax,
        AdjustmentInterface $unit2Tax,
        AdjustmentInterface $unit3Tax,
    ): void {
        $orderItem->getAdjustments(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([]));

        $orderItem->getUnits()->willReturn(new ArrayCollection([
            $unit1->getWrappedObject(),
            $unit2->getWrappedObject(),
            $unit3->getWrappedObject(),
        ]));

        $unit1->getAdjustments(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([$unit1Tax->getWrappedObject()]));
        $unit2->getAdjustments(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([$unit2Tax->getWrappedObject()]));
        $unit3->getAdjustments(AdjustmentInterface::TAX_ADJUSTMENT)
            ->willReturn(new ArrayCollection([$unit3Tax->getWrappedObject()]));

        $unit1Tax->isNeutral()->willReturn(false);
        $unit1Tax->getAmount()->willReturn(100);
        $unit2Tax->isNeutral()->willReturn(false);
        $unit2Tax->getAmount()->willReturn(100);
        $unit3Tax->isNeutral()->willReturn(false);
        $unit3Tax->getAmount()->willReturn(100);

        $this->provide($orderItem)->shouldReturn([100, 100, 100]);
    }
}
