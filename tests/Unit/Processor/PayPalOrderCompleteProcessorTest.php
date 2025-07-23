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

namespace Tests\Sylius\PayPalPlugin\Unit\Processor;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Sylius\PayPalPlugin\Manager\PaymentStateManagerInterface;
use Sylius\PayPalPlugin\Processor\PayPalOrderCompleteProcessor;
use Sylius\PayPalPlugin\Verifier\PaymentAmountVerifierInterface;

final class PayPalOrderCompleteProcessorTest extends TestCase
{
    private PayPalOrderCompleteProcessor $paypalOrderCompleteProcessor;

    private PaymentStateManagerInterface&MockObject $paymentStateManager;

    private PaymentAmountVerifierInterface&MockObject $paymentAmountVerifier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentStateManager = $this->createMock(PaymentStateManagerInterface::class);
        $this->paymentAmountVerifier = $this->createMock(PaymentAmountVerifierInterface::class);

        $this->paypalOrderCompleteProcessor = new PayPalOrderCompleteProcessor(
            $this->paymentStateManager,
            $this->paymentAmountVerifier,
        );
    }

    public function testItCompletesPaypalOrder(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $order->method('getLastPayment')->with(PaymentInterface::STATE_PROCESSING)->willReturn($payment);

        $payment->method('getMethod')->willReturn($paymentMethod);
        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);
        $gatewayConfig->method('getFactoryName')->willReturn('sylius_paypal');

        $this->paymentAmountVerifier->expects(self::once())->method('verify')->with($payment);
        $this->paymentStateManager->expects(self::once())->method('complete')->with($payment);

        $this->paypalOrderCompleteProcessor->completePayPalOrder($order);
    }

    public function testItDoesNothingIfProcessingPaymentIsNotPaypal(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $order->method('getLastPayment')->with(PaymentInterface::STATE_PROCESSING)->willReturn($payment);

        $payment->method('getMethod')->willReturn($paymentMethod);
        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);
        $gatewayConfig->method('getFactoryName')->willReturn('stripe');

        $this->paymentStateManager->expects($this->never())->method('complete');

        $this->paypalOrderCompleteProcessor->completePayPalOrder($order);
    }

    public function testItDoesNothingIfThereIsNoProcessingPaymentForTheOrder(): void
    {
        $order = $this->createMock(OrderInterface::class);

        $order->method('getLastPayment')->with(PaymentInterface::STATE_PROCESSING)->willReturn(null);

        $this->paymentStateManager->expects($this->never())->method('complete');

        $this->paypalOrderCompleteProcessor->completePayPalOrder($order);
    }
}
