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

use Sylius\Component\Core\Model\OrderInterface;

trigger_deprecation(
    'sylius/paypal-plugin',
    '1.7',
    'The "%s" class is deprecated and will be removed in Sylius/PayPalPlugin 3.0.',
    PayPalAddressProcessorInterface::class,
);

/** @deprecated since Sylius/PayPalPlugin 1.7 and will be removed in Sylius/PayPalPlugin 3.0. */
interface PayPalAddressProcessorInterface
{
    /**
     * @param array<string, string> $address
     */
    public function process(array $address, OrderInterface $order): void;
}
