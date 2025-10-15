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

namespace Sylius\PayPalPlugin\Resolver;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Sylius\PayPalPlugin\DependencyInjection\SyliusPayPalExtension;

final class PayPalPaymentMethodsResolver implements PayPalPaymentMethodsResolverInterface
{
    public function __construct(
        private readonly PaymentMethodRepositoryInterface $paymentMethodRepository,
    ) {
    }

    public function getInChannel(ChannelInterface $channel): array
    {
        $enabledMethods = $this->paymentMethodRepository->findEnabledForChannel($channel);

        $paypalMethods = [];
        /** @var PaymentMethodInterface $method */
        foreach ($enabledMethods as $method) {
            if ($method->getGatewayConfig()?->getFactoryName() === SyliusPayPalExtension::PAYPAL_FACTORY_NAME) {
                $paypalMethods[] = $method;
            }
        }

        usort(
            $paypalMethods,
            fn (PaymentMethodInterface $a, PaymentMethodInterface $b) => $a->getPosition() <=> $b->getPosition(),
        );

        return $paypalMethods;
    }
}
