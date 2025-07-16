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

use Doctrine\Persistence\ObjectManager;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Sylius\PayPalPlugin\Provider\FlashBagProvider;
use Sylius\PayPalPlugin\Provider\PaymentProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

final readonly class CancelPayPalPaymentAction
{
    public function __construct(
        private PaymentProviderInterface $paymentProvider,
        private ObjectManager $objectManager,
        private RequestStack $flashBagOrRequestStack,
        private StateMachineInterface $stateMachineFactory,
        private OrderProcessorInterface $orderPaymentProcessor,
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

        $this->stateMachineFactory->apply($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_CANCEL);

        $this->orderPaymentProcessor->process($order);
        $this->objectManager->flush();

        FlashBagProvider::getFlashBag($this->flashBagOrRequestStack)
            ->add('success', 'sylius_paypal.payment_cancelled')
        ;

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
