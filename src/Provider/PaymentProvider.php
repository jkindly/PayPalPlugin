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
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Sylius\PayPalPlugin\Exception\PaymentNotFoundException;

final readonly class PaymentProvider implements PaymentProviderInterface
{
    public function __construct(private PaymentRepositoryInterface $paymentRepository)
    {
    }

    public function getByPayPalOrderId(string $orderId): PaymentInterface
    {
        /** @var PaymentInterface[] $payments */
        $payments = $this->paymentRepository->findAll();

        foreach ($payments as $payment) {
            $details = $payment->getDetails();

            if (isset($details['paypal_order_id']) && $details['paypal_order_id'] === $orderId) {
                return $payment;
            }
        }

        throw PaymentNotFoundException::withPayPalOrderId($orderId);
    }
}
