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

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;

interface OrderPayPalPaymentProviderInterface
{
    /**
     * Returns the order's PayPal payment that carries the identifiers needed to add tracking
     * (paypal_order_id and transaction_id), or null when the order was not paid with PayPal.
     */
    public function provide(OrderInterface $order): ?PaymentInterface;
}
