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
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\PayPalPlugin\Provider\OrderItemNonNeutralTaxesProviderInterface;
use Sylius\PayPalPlugin\Provider\PayPalItemDataProvider;

final class PayPalItemDataProviderTest extends TestCase
{
    private OrderItemNonNeutralTaxesProviderInterface $orderItemNonNeutralTaxesProvider;
    private PayPalItemDataProvider $provider;

    protected function setUp(): void
    {
        $this->orderItemNonNeutralTaxesProvider = $this->createMock(OrderItemNonNeutralTaxesProviderInterface::class);
        
        $this->provider = new PayPalItemDataProvider($this->orderItemNonNeutralTaxesProvider);
    }

    public function testReturnsArrayOfItemsWithTax(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $orderItem = $this->createMock(OrderItemInterface::class);

        $order->method('getItems')->willReturn(new ArrayCollection([$orderItem]));
        $orderItem->method('getProductName')->willReturn('PRODUCT_ONE');
        $order->method('getCurrencyCode')->willReturn('PLN');

        $orderItem->method('getUnitPrice')->willReturn(2000);
        $orderItem->method('getQuantity')->willReturn(1);

        $this->orderItemNonNeutralTaxesProvider->method('provide')->with($orderItem)->willReturn([200]);

        $result = $this->provider->provide($order);

        $expected = [
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
        ];

        $this->assertEquals($expected, $result);
    }

    public function testReturnsArrayOfItemsWithDifferentQuantitiesWithTax(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $orderItem = $this->createMock(OrderItemInterface::class);

        $order->method('getItems')->willReturn(new ArrayCollection([$orderItem]));
        $orderItem->method('getProductName')->willReturn('PRODUCT_ONE');
        $order->method('getCurrencyCode')->willReturn('PLN');

        $orderItem->method('getUnitPrice')->willReturn(2000);
        $orderItem->method('getQuantity')->willReturn(3);

        $this->orderItemNonNeutralTaxesProvider->method('provide')->with($orderItem)->willReturn([200, 200, 200]);

        $result = $this->provider->provide($order);

        $expected = [
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
            'total_item_value' => '60.00',
            'total_tax' => '6.00',
        ];

        $this->assertEquals($expected, $result);
    }

    public function testReturnsArrayOfItemsWithDifferentQuantitiesWithoutTax(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $orderItem = $this->createMock(OrderItemInterface::class);

        $order->method('getItems')->willReturn(new ArrayCollection([$orderItem]));
        $orderItem->method('getProductName')->willReturn('PRODUCT_ONE');
        $order->method('getCurrencyCode')->willReturn('PLN');

        $orderItem->method('getUnitPrice')->willReturn(2000);
        $orderItem->method('getQuantity')->willReturn(3);

        $this->orderItemNonNeutralTaxesProvider->method('provide')->with($orderItem)->willReturn([0]);

        $result = $this->provider->provide($order);

        $expected = [
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
        ];

        $this->assertEquals($expected, $result);
    }

    public function testReturnsArrayOfDifferentItemsWithDifferentQuantitiesWithoutTax(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $orderItemOne = $this->createMock(OrderItemInterface::class);
        $orderItemTwo = $this->createMock(OrderItemInterface::class);

        $order->method('getItems')->willReturn(new ArrayCollection([$orderItemOne, $orderItemTwo]));
        $orderItemOne->method('getProductName')->willReturn('PRODUCT_ONE');
        $orderItemOne->method('getUnitPrice')->willReturn(2000);
        $orderItemOne->method('getQuantity')->willReturn(3);

        $orderItemTwo->method('getProductName')->willReturn('PRODUCT_TWO');
        $orderItemTwo->method('getUnitPrice')->willReturn(1000);
        $orderItemTwo->method('getQuantity')->willReturn(2);

        $order->method('getCurrencyCode')->willReturn('PLN');

        $this->orderItemNonNeutralTaxesProvider->method('provide')
            ->willReturnMap([
                [$orderItemOne, [0]],
                [$orderItemTwo, [0]],
            ]);

        $result = $this->provider->provide($order);

        $expected = [
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
        ];

        $this->assertEquals($expected, $result);
    }

    public function testReturnsArrayOfDifferentItemsWithDifferentQuantitiesWithTax(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $orderItemOne = $this->createMock(OrderItemInterface::class);
        $orderItemTwo = $this->createMock(OrderItemInterface::class);

        $order->method('getItems')->willReturn(new ArrayCollection([$orderItemOne, $orderItemTwo]));
        $orderItemOne->method('getProductName')->willReturn('PRODUCT_ONE');
        $orderItemOne->method('getUnitPrice')->willReturn(2000);
        $orderItemOne->method('getQuantity')->willReturn(3);

        $orderItemTwo->method('getProductName')->willReturn('PRODUCT_TWO');
        $orderItemTwo->method('getUnitPrice')->willReturn(1000);
        $orderItemTwo->method('getQuantity')->willReturn(2);

        $order->method('getCurrencyCode')->willReturn('PLN');

        $this->orderItemNonNeutralTaxesProvider->method('provide')
            ->willReturnMap([
                [$orderItemOne, [100, 100, 100]],
                [$orderItemTwo, [200, 100]],
            ]);

        $result = $this->provider->provide($order);

        $expected = [
            'items' => [
                [
                    'name' => 'PRODUCT_ONE',
                    'unit_amount' => [
                        'value' => '20.00',
                        'currency_code' => 'PLN',
                    ],
                    'quantity' => 1,
                    'tax' => [
                        'value' => '1.00',
                        'currency_code' => 'PLN',
                    ],
                ],
                [
                    'name' => 'PRODUCT_ONE',
                    'unit_amount' => [
                        'value' => '20.00',
                        'currency_code' => 'PLN',
                    ],
                    'quantity' => 1,
                    'tax' => [
                        'value' => '1.00',
                        'currency_code' => 'PLN',
                    ],
                ],
                [
                    'name' => 'PRODUCT_ONE',
                    'unit_amount' => [
                        'value' => '20.00',
                        'currency_code' => 'PLN',
                    ],
                    'quantity' => 1,
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
                    'quantity' => 1,
                    'tax' => [
                        'value' => '2.00',
                        'currency_code' => 'PLN',
                    ],
                ],
                [
                    'name' => 'PRODUCT_TWO',
                    'unit_amount' => [
                        'value' => '10.00',
                        'currency_code' => 'PLN',
                    ],
                    'quantity' => 1,
                    'tax' => [
                        'value' => '1.00',
                        'currency_code' => 'PLN',
                    ],
                ],
            ],
            'total_item_value' => '80.00',
            'total_tax' => '6.00',
        ];

        $this->assertEquals($expected, $result);
    }
}