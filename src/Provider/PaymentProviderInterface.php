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

use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\PayPalPlugin\Exception\PaymentNotFoundException;
use Sylius\PayPalPlugin\Repository\Query\PaypalPaymentQueryInterface;

trigger_deprecation(
    'sylius/paypal-plugin',
    '1.7',
    'The "%s" interface is deprecated and will be removed in Sylius/PayPalPlugin 3.0. Use "%s" instead.',
    PaymentProviderInterface::class,
    PaypalPaymentQueryInterface::class,
);
/** @deprecated since Sylius/PayPalPlugin 1.7 and will be removed in Sylius/PayPalPlugin 3.0. */
interface PaymentProviderInterface
{
    /** @throws PaymentNotFoundException */
    public function getByPayPalOrderId(string $orderId): PaymentInterface;
}
