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

namespace Sylius\PayPalPlugin\Provider;

use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;

final readonly class PayPalItemDataProvider implements PayPalItemDataProviderInterface
{
    public function __construct(private OrderItemNonNeutralTaxesProviderInterface $orderItemNonNeutralTaxesProvider)
    {
    }

    public function provide(OrderInterface $order): array
    {
        $itemData = [
            'items' => [],
            'total_item_value' => 0,
            'total_tax' => 0,
        ];

        $currencyCode = (string) $order->getCurrencyCode();

        /** @var Collection<int, OrderItemInterface> $orderItems */
        $orderItems = $order->getItems();

        foreach ($orderItems as $orderItem) {
            $productName = $this->truncateProductName($orderItem->getProductName());
            $quantity = $orderItem->getQuantity();
            if ($quantity <= 0) {
                continue;
            }

            $itemValue = $orderItem->getUnitPrice();

            $nonNeutralTaxes = $this->orderItemNonNeutralTaxesProvider->provide($orderItem);
            $totalTax = $nonNeutralTaxes !== [] ? array_sum($nonNeutralTaxes) : 0;

            $baseTax = (int) floor($totalTax / $quantity);
            $remainder = $totalTax % $quantity;

            if ($remainder === 0 || $quantity === 1) {
                $this->addItem($itemData, $productName, $quantity, $itemValue, $baseTax, $currencyCode);
            } else {
                $this->addItem($itemData, $productName, $quantity - 1, $itemValue, $baseTax, $currencyCode);
                $this->addItem($itemData, $productName, 1, $itemValue, $baseTax + $remainder, $currencyCode);
            }
        }

        $itemData['total_item_value'] = number_format($itemData['total_item_value'] / 100, 2, '.', '');
        $itemData['total_tax'] = number_format($itemData['total_tax'] / 100, 2, '.', '');

        return $itemData;
    }

    private function addItem(
        array &$itemData,
        string $productName,
        int $quantity,
        int $itemValue,
        int $tax,
        string $currencyCode,
    ): void {
        $itemData['total_item_value'] += $itemValue * $quantity;
        $itemData['total_tax'] += $tax * $quantity;

        $itemData['items'][] = [
            'name' => $productName,
            'unit_amount' => [
                'value' => number_format($itemValue / 100, 2, '.', ''),
                'currency_code' => $currencyCode,
            ],
            'quantity' => $quantity,
            'tax' => [
                'value' => number_format($tax / 100, 2, '.', ''),
                'currency_code' => $currencyCode,
            ],
        ];
    }

    private function truncateProductName(string $productName): string
    {
        return mb_strlen($productName) > 127
            ? mb_substr($productName, 0, 124) . '...'
            : $productName;
    }
}
