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
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\PayPalPlugin\Provider\OrderItemNonNeutralTaxesProviderInterface;

final class PayPalItemDataProviderSpec extends ObjectBehavior
{
    function let(OrderItemNonNeutralTaxesProviderInterface $orderItemNonNeutralTaxesProvider): void
    {
        $this->beConstructedWith($orderItemNonNeutralTaxesProvider);
    }

    function it_returns_array_of_items_with_tax(
        OrderInterface $order,
        OrderItemInterface $orderItem,
        OrderItemNonNeutralTaxesProviderInterface $orderItemNonNeutralTaxesProvider,
    ): void {
        $order->getItems()->willReturn(new ArrayCollection([$orderItem->getWrappedObject()]));
        $orderItem->getProductName()->willReturn('PRODUCT_ONE');
        $order->getCurrencyCode()->willReturn('PLN');

        $orderItem->getUnitPrice()->willReturn(2000);
        $orderItem->getQuantity()->willReturn(1);

        $orderItemNonNeutralTaxesProvider->provide($orderItem)->willReturn([200]);

        $this->provide($order)->shouldReturn(
            [
                'items' => [
                    [
                        'name' => 'PRODUCT_ONE',
                        'unit_amount' => [
                            'value' => '20.00',
                            'currency_code' => 'PLN',
                        ],
                        'quantity' => 1,
                        'tax' => [
                            'value' => '2.00',
                            'currency_code' => 'PLN',
                        ],
                    ],
                ],
                'total_item_value' => '20.00',
                'total_tax' => '2.00',
            ],
        );
    }

    function it_returns_array_of_items_with_different_quantities_with_tax(
        OrderInterface $order,
        OrderItemInterface $orderItem,
        OrderItemNonNeutralTaxesProviderInterface $orderItemNonNeutralTaxesProvider,
    ): void {
        $order->getItems()->willReturn(new ArrayCollection([$orderItem->getWrappedObject()]));
        $orderItem->getProductName()->willReturn('PRODUCT_ONE');
        $order->getCurrencyCode()->willReturn('PLN');

        $orderItem->getUnitPrice()->willReturn(2000);
        $orderItem->getQuantity()->willReturn(3);

        $orderItemNonNeutralTaxesProvider->provide($orderItem)->willReturn([200, 200, 200]);

        $this->provide($order)->shouldReturn(
            [
                'items' => [
                    [
                        'name' => 'PRODUCT_ONE',
                        'unit_amount' => [
                            'value' => '20.00',
                            'currency_code' => 'PLN',
                        ],
                        'quantity' => 3,
                        'tax' => [
                            'value' => '2.00',
                            'currency_code' => 'PLN',
                        ],
                    ],
                ],
                'total_item_value' => '60.00',
                'total_tax' => '6.00',
            ],
        );
    }

    function it_returns_array_of_items_with_different_quantities_without_tax(
        OrderInterface $order,
        OrderItemInterface $orderItem,
        OrderItemNonNeutralTaxesProviderInterface $orderItemNonNeutralTaxesProvider,
    ): void {
        $order->getItems()->willReturn(new ArrayCollection([$orderItem->getWrappedObject()]));
        $orderItem->getProductName()->willReturn('PRODUCT_ONE');
        $order->getCurrencyCode()->willReturn('PLN');

        $orderItem->getUnitPrice()->willReturn(2000);
        $orderItem->getQuantity()->willReturn(3);

        $orderItemNonNeutralTaxesProvider->provide($orderItem)->willReturn([]);

        $this->provide($order)->shouldReturn(
            [
                'items' => [
                    [
                        'name' => 'PRODUCT_ONE',
                        'unit_amount' => [
                            'value' => '20.00',
                            'currency_code' => 'PLN',
                        ],
                        'quantity' => 3,
                        'tax' => [
                            'value' => '0.00',
                            'currency_code' => 'PLN',
                        ],
                    ],
                ],
                'total_item_value' => '60.00',
                'total_tax' => '0.00',
            ],
        );
    }

    function it_returns_array_of_different_items_with_different_quantities_without_tax(
        OrderInterface $order,
        OrderItemInterface $orderItemOne,
        OrderItemInterface $orderItemTwo,
        OrderItemNonNeutralTaxesProviderInterface $orderItemNonNeutralTaxesProvider,
    ): void {
        $order->getItems()
            ->willReturn(new ArrayCollection([$orderItemOne->getWrappedObject(), $orderItemTwo->getWrappedObject()]));
        $orderItemOne->getProductName()->willReturn('PRODUCT_ONE');
        $orderItemOne->getUnitPrice()->willReturn(2000);
        $orderItemOne->getQuantity()->willReturn(3);

        $orderItemTwo->getProductName()->willReturn('PRODUCT_TWO');
        $orderItemTwo->getUnitPrice()->willReturn(1000);
        $orderItemTwo->getQuantity()->willReturn(2);

        $order->getCurrencyCode()->willReturn('PLN');

        $orderItemNonNeutralTaxesProvider->provide($orderItemOne)->willReturn([]);
        $orderItemNonNeutralTaxesProvider->provide($orderItemTwo)->willReturn([]);

        $this->provide($order)->shouldReturn(
            [
                'items' => [
                    [
                        'name' => 'PRODUCT_ONE',
                        'unit_amount' => [
                            'value' => '20.00',
                            'currency_code' => 'PLN',
                        ],
                        'quantity' => 3,
                        'tax' => [
                            'value' => '0.00',
                            'currency_code' => 'PLN',
                        ],
                    ],
                    [
                        'name' => 'PRODUCT_TWO',
                        'unit_amount' => [
                            'value' => '10.00',
                            'currency_code' => 'PLN',
                        ],
                        'quantity' => 2,
                        'tax' => [
                            'value' => '0.00',
                            'currency_code' => 'PLN',
                        ],
                    ],
                ],
                'total_item_value' => '80.00',
                'total_tax' => '0.00',
            ],
        );
    }

    function it_returns_array_of_different_items_with_different_quantities_with_tax(
        OrderInterface $order,
        OrderItemInterface $orderItemOne,
        OrderItemInterface $orderItemTwo,
        OrderItemNonNeutralTaxesProviderInterface $orderItemNonNeutralTaxesProvider,
    ): void {
        $order->getItems()->willReturn(new ArrayCollection([$orderItemOne->getWrappedObject(), $orderItemTwo->getWrappedObject()]));
        $orderItemOne->getProductName()->willReturn('PRODUCT_ONE');
        $orderItemOne->getUnitPrice()->willReturn(2000);
        $orderItemOne->getQuantity()->willReturn(3);

        $orderItemTwo->getProductName()->willReturn('PRODUCT_TWO');
        $orderItemTwo->getUnitPrice()->willReturn(1000);
        $orderItemTwo->getQuantity()->willReturn(2);

        $order->getCurrencyCode()->willReturn('PLN');

        $orderItemNonNeutralTaxesProvider->provide($orderItemOne)->willReturn([100, 100, 100]);
        $orderItemNonNeutralTaxesProvider->provide($orderItemTwo)->willReturn([200, 100]);

        $this->provide($order)->shouldReturn(
            [
                'items' => [
                    [
                        'name' => 'PRODUCT_ONE',
                        'unit_amount' => [
                            'value' => '20.00',
                            'currency_code' => 'PLN',
                        ],
                        'quantity' => 3,
                        'tax' => [
                            'value' => '1.00',
                            'currency_code' => 'PLN',
                        ],
                    ],
                    [
                        'name' => 'PRODUCT_TWO',
                        'unit_amount' => [
                            'value' => '10.00',
                            'currency_code' => 'PLN',
                        ],
                        'quantity' => 2,
                        'tax' => [
                            'value' => '1.50',
                            'currency_code' => 'PLN',
                        ],
                    ],
                ],
                'total_item_value' => '80.00',
                'total_tax' => '6.00',
            ],
        );
    }

    function it_splits_items_when_tax_is_not_evenly_divisible(
        OrderInterface $order,
        OrderItemInterface $orderItem,
        OrderItemNonNeutralTaxesProviderInterface $orderItemNonNeutralTaxesProvider,
    ): void {
        $order->getItems()->willReturn(new ArrayCollection([$orderItem->getWrappedObject()]));
        $orderItem->getProductName()->willReturn('PRODUCT_WITH_NON_DIVISIBLE_TAX');
        $order->getCurrencyCode()->willReturn('USD');

        $orderItem->getUnitPrice()->willReturn(1500);
        $orderItem->getQuantity()->willReturn(3);

        $orderItemNonNeutralTaxesProvider->provide($orderItem)->willReturn([166, 167, 167]);

        $this->provide($order)->shouldReturn(
            [
                'items' => [
                    [
                        'name' => 'PRODUCT_WITH_NON_DIVISIBLE_TAX',
                        'unit_amount' => [
                            'value' => '15.00',
                            'currency_code' => 'USD',
                        ],
                        'quantity' => 2,
                        'tax' => [
                            'value' => '1.66',
                            'currency_code' => 'USD',
                        ],
                    ],
                    [
                        'name' => 'PRODUCT_WITH_NON_DIVISIBLE_TAX',
                        'unit_amount' => [
                            'value' => '15.00',
                            'currency_code' => 'USD',
                        ],
                        'quantity' => 1,
                        'tax' => [
                            'value' => '1.68',
                            'currency_code' => 'USD',
                        ],
                    ],
                ],
                'total_item_value' => '45.00',
                'total_tax' => '5.00',
            ],
        );
    }

    function it_handles_complex_non_divisible_tax_scenario_with_multiple_items(
        OrderInterface $order,
        OrderItemInterface $orderItemOne,
        OrderItemInterface $orderItemTwo,
        OrderItemNonNeutralTaxesProviderInterface $orderItemNonNeutralTaxesProvider,
    ): void {
        $order->getItems()->willReturn(new ArrayCollection([
            $orderItemOne->getWrappedObject(),
            $orderItemTwo->getWrappedObject(),
        ]));

        $orderItemOne->getProductName()->willReturn('PRODUCT_ONE');
        $orderItemOne->getUnitPrice()->willReturn(2000);
        $orderItemOne->getQuantity()->willReturn(3);

        $orderItemTwo->getProductName()->willReturn('PRODUCT_TWO');
        $orderItemTwo->getUnitPrice()->willReturn(1000);
        $orderItemTwo->getQuantity()->willReturn(3);

        $order->getCurrencyCode()->willReturn('EUR');

        $orderItemNonNeutralTaxesProvider->provide($orderItemOne)->willReturn([100, 100, 100]);

        $orderItemNonNeutralTaxesProvider->provide($orderItemTwo)->willReturn([166, 167, 167]);

        $this->provide($order)->shouldReturn(
            [
                'items' => [
                    [
                        'name' => 'PRODUCT_ONE',
                        'unit_amount' => [
                            'value' => '20.00',
                            'currency_code' => 'EUR',
                        ],
                        'quantity' => 3,
                        'tax' => [
                            'value' => '1.00',
                            'currency_code' => 'EUR',
                        ],
                    ],
                    [
                        'name' => 'PRODUCT_TWO',
                        'unit_amount' => [
                            'value' => '10.00',
                            'currency_code' => 'EUR',
                        ],
                        'quantity' => 2,
                        'tax' => [
                            'value' => '1.66',
                            'currency_code' => 'EUR',
                        ],
                    ],
                    [
                        'name' => 'PRODUCT_TWO',
                        'unit_amount' => [
                            'value' => '10.00',
                            'currency_code' => 'EUR',
                        ],
                        'quantity' => 1,
                        'tax' => [
                            'value' => '1.68',
                            'currency_code' => 'EUR',
                        ],
                    ],
                ],
                'total_item_value' => '90.00',
                'total_tax' => '8.00',
            ],
        );
    }

    function it_handles_single_cent_remainder_distribution(
        OrderInterface $order,
        OrderItemInterface $orderItem,
        OrderItemNonNeutralTaxesProviderInterface $orderItemNonNeutralTaxesProvider,
    ): void {
        $order->getItems()->willReturn(new ArrayCollection([$orderItem->getWrappedObject()]));
        $orderItem->getProductName()->willReturn('PRODUCT');
        $order->getCurrencyCode()->willReturn('GBP');

        $orderItem->getUnitPrice()->willReturn(1000);
        $orderItem->getQuantity()->willReturn(2);

        $orderItemNonNeutralTaxesProvider->provide($orderItem)->willReturn([50, 51]);

        $this->provide($order)->shouldReturn(
            [
                'items' => [
                    [
                        'name' => 'PRODUCT',
                        'unit_amount' => [
                            'value' => '10.00',
                            'currency_code' => 'GBP',
                        ],
                        'quantity' => 1,
                        'tax' => [
                            'value' => '0.50',
                            'currency_code' => 'GBP',
                        ],
                    ],
                    [
                        'name' => 'PRODUCT',
                        'unit_amount' => [
                            'value' => '10.00',
                            'currency_code' => 'GBP',
                        ],
                        'quantity' => 1,
                        'tax' => [
                            'value' => '0.51',
                            'currency_code' => 'GBP',
                        ],
                    ],
                ],
                'total_item_value' => '20.00',
                'total_tax' => '1.01',
            ],
        );
    }
}
