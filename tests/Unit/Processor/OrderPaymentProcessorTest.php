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

use PHPUnit\Framework\TestCase;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Sylius\PayPalPlugin\Processor\OrderPaymentProcessor;

final class OrderPaymentProcessorTest extends TestCase
{
    private OrderPaymentProcessor $orderPaymentProcessor;
    private OrderProcessorInterface $baseOrderProcessor;
    private StateMachineInterface $stateMachine;

    protected function setUp(): void
    {
        $this->baseOrderProcessor = $this->createMock(OrderProcessorInterface::class);
        $this->stateMachine = $this->createMock(StateMachineInterface::class);
        
        $this->orderPaymentProcessor = new OrderPaymentProcessor(
            $this->baseOrderProcessor,
            $this->stateMachine
        );
    }

    public function testItImplementsOrderProcessorInterface(): void
    {
        $this->assertInstanceOf(OrderProcessorInterface::class, $this->orderPaymentProcessor);
    }

    public function testItDoesNothingIfThereIsAPaypalProcessingCapturedPayment(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $order->method('getLastPayment')->with(PaymentInterface::STATE_PROCESSING)->willReturn($payment);
        $payment->method('getDetails')->willReturn(['status' => 'CAPTURED']);
        $payment->method('getMethod')->willReturn($paymentMethod);
        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);
        $gatewayConfig->method('getFactoryName')->willReturn('sylius_paypal');

        $this->baseOrderProcessor->expects($this->never())->method('process');

        $this->orderPaymentProcessor->process($order);
    }

    public function testItProcessesOrderIfThereIsNoProcessingPayment(): void
    {
        $order = $this->createMock(OrderInterface::class);

        $order->method('getLastPayment')->with(PaymentInterface::STATE_PROCESSING)->willReturn(null);

        $this->baseOrderProcessor->expects($this->once())->method('process')->with($order);

        $this->orderPaymentProcessor->process($order);
    }

    public function testItProcessesOrderIfTheProcessingPaymentIsNotCaptured(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $order->method('getLastPayment')->with(PaymentInterface::STATE_PROCESSING)->willReturn($payment);
        $payment->method('getDetails')->willReturn(['status' => 'CANCELLED']);
        $payment->method('getMethod')->willReturn($paymentMethod);
        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);
        $gatewayConfig->method('getFactoryName')->willReturn('sylius_paypal');

        $this->baseOrderProcessor->expects($this->once())->method('process')->with($order);

        $this->orderPaymentProcessor->process($order);
    }

    public function testItCancelsPaymentAndProcessesOrderIfTheProcessingPaymentHasMethodChangeToNonPaypal(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $payment = $this->createMock(PaymentInterface::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $order->method('getLastPayment')->with(PaymentInterface::STATE_PROCESSING)->willReturn($payment);
        $payment->method('getDetails')->willReturn(['status' => 'CANCELLED']);
        $payment->method('getMethod')->willReturn($paymentMethod);
        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);
        $gatewayConfig->method('getFactoryName')->willReturn('offline');

        $this->stateMachine->expects($this->once())
            ->method('apply')
            ->with($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_CANCEL);

        $this->baseOrderProcessor->expects($this->once())->method('process')->with($order);

        $this->orderPaymentProcessor->process($order);
    }
}