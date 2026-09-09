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

use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\PayPalPlugin\DependencyInjection\SyliusPayPalExtension;

final class OrderPayPalPaymentProvider implements OrderPayPalPaymentProviderInterface
{
    public function provide(OrderInterface $order): ?PaymentInterface
    {
        foreach ($order->getPayments() as $payment) {
            if (!$payment instanceof PaymentInterface) {
                continue;
            }

            $method = $payment->getMethod();
            if (!$method instanceof PaymentMethodInterface) {
                continue;
            }

            $gatewayConfig = $method->getGatewayConfig();
            if (!$gatewayConfig instanceof GatewayConfigInterface) {
                continue;
            }

            if ($gatewayConfig->getFactoryName() !== SyliusPayPalExtension::PAYPAL_FACTORY_NAME) {
                continue;
            }

            $details = $payment->getDetails();
            if (isset($details['paypal_order_id'])) {
                return $payment;
            }
        }

        return null;
    }
}
