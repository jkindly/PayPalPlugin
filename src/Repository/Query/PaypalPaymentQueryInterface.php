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

namespace Sylius\PayPalPlugin\Repository\Query;

use Sylius\Component\Core\Model\PaymentInterface;

interface PaypalPaymentQueryInterface
{
    public function getForUpdateByOrderId(string $paypalOrderId): ?PaymentInterface;

    public function getForCancellationByOrderId(string $paypalOrderId): ?PaymentInterface;

    public function getForRefundingByOrderId(string $paypalOrderId): ?PaymentInterface;
}
