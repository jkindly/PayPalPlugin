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
use Sylius\PayPalPlugin\Repository\Query\PaypalPaymentQuery;

trigger_deprecation(
    'sylius/paypal-plugin',
    '1.7',
    'The "%s" class is deprecated and will be removed in Sylius/PayPalPlugin 3.0. Use "%s" instead.',
    PaymentProvider::class,
    PaypalPaymentQuery::class,
);
/** @deprecated since Sylius/PayPalPlugin 1.7 and will be removed in Sylius/PayPalPlugin 3.0. */
final class PaymentProvider implements PaymentProviderInterface
{
    private PaymentRepositoryInterface $paymentRepository;

    public function __construct(PaymentRepositoryInterface $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
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
