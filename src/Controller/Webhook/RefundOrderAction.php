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

namespace Sylius\PayPalPlugin\Controller\Webhook;

use Doctrine\Persistence\ObjectManager;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Sylius\PayPalPlugin\Exception\PaymentNotFoundException;
use Sylius\PayPalPlugin\Exception\PayPalWrongDataException;
use Sylius\PayPalPlugin\Provider\PaymentProviderInterface;
use Sylius\PayPalPlugin\Provider\PayPalRefundDataProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

final readonly class RefundOrderAction
{
    public function __construct(
        private StateMachineInterface $stateMachineFactory,
        private PaymentProviderInterface $paymentProvider,
        private ObjectManager $paymentManager,
        private PayPalRefundDataProviderInterface $payPalRefundDataProvider,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $refundData = $this->payPalRefundDataProvider->provide($this->getPayPalPaymentUrl($request));

        try {
            $payment = $this->paymentProvider->getByPayPalOrderId((string) $refundData['id']);
        } catch (PaymentNotFoundException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        }

        if ($this->stateMachineFactory->can($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_REFUND)) {
            $this->stateMachineFactory->apply($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_REFUND);
        }

        $this->paymentManager->flush();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    private function getPayPalPaymentUrl(Request $request): string
    {
        /**
         * @var string $content
         */
        $content = $request->getContent();

        $content = (array) json_decode($content, true);
        Assert::keyExists($content, 'resource');
        $resource = (array) $content['resource'];
        Assert::keyExists($resource, 'links');

        /** @var string[] $link */
        foreach ($resource['links'] as $link) {
            if ($link['rel'] === 'up') {
                return $link['href'];
            }
        }

        throw new PayPalWrongDataException();
    }
}
