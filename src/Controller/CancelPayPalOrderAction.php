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

namespace Sylius\PayPalPlugin\Controller;

use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\PayPalPlugin\Provider\FlashBagProvider;
use Sylius\PayPalPlugin\Provider\PaymentProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

final class CancelPayPalOrderAction
{
    public function __construct(
        private readonly ?PaymentProviderInterface $paymentProvider,
        private readonly ?OrderRepositoryInterface $orderRepository,
        private readonly FlashBag|RequestStack $flashBagOrRequestStack,
    ) {
        if ($flashBagOrRequestStack instanceof FlashBag) {
            trigger_deprecation('sylius/paypal-plugin', '1.5', sprintf('Passing an instance of %s as constructor argument for %s is deprecated as of PayPalPlugin 1.5 and will be removed in 2.0. Pass an instance of %s instead.', FlashBag::class, self::class, RequestStack::class));
        }
        if (null !== $this->paymentProvider) {
            trigger_deprecation('sylius/paypal-plugin', '1.7', sprintf('Passing an instance of %s as the first argument is deprecated and will be prohibited in 3.0', PaymentProviderInterface::class));
        }
        if (null !== $this->orderRepository) {
            trigger_deprecation('sylius/paypal-plugin', '1.7', sprintf('Passing an instance of %s as the second argument is deprecated and will be prohibited in 3.0', OrderRepositoryInterface::class));
        }
    }

    public function __invoke(Request $request): Response
    {
        FlashBagProvider::getFlashBag($this->flashBagOrRequestStack)->add('success', 'sylius.pay_pal.order_cancelled');

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
