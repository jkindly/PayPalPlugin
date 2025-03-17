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

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\PayPalPlugin\Provider\FlashBagProvider;
use Sylius\PayPalPlugin\Provider\PaymentProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

final readonly class CancelPayPalOrderAction
{
    public function __construct(
        private PaymentProviderInterface $paymentProvider,
        private OrderRepositoryInterface $orderRepository,
        private RequestStack $flashBagOrRequestStack,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        /**
         * @var string $content
         */
        $content = $request->getContent();

        $content = (array) json_decode($content, true);

        $payment = $this->paymentProvider->getByPayPalOrderId((string) $content['payPalOrderId']);

        /** @var OrderInterface $order */
        $order = $payment->getOrder();
        $this->orderRepository->remove($order);

        FlashBagProvider::getFlashBag($this->flashBagOrRequestStack)->add('success', 'sylius_paypal.order_cancelled');

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
