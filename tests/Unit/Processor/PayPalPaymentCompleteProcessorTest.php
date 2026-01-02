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

use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Sylius\PayPalPlugin\Payum\Request\CompleteOrder;
use Sylius\PayPalPlugin\Processor\PaymentCompleteProcessorInterface;
use Sylius\PayPalPlugin\Processor\PayPalPaymentCompleteProcessor;

final class PayPalPaymentCompleteProcessorTest extends TestCase
{
    private PayPalPaymentCompleteProcessor $paypalPaymentCompleteProcessor;

    private Payum&MockObject $payum;

    protected function setUp(): void
    {
        parent::setUp();
        $this->payum = $this->createMock(Payum::class);

        $this->paypalPaymentCompleteProcessor = new PayPalPaymentCompleteProcessor($this->payum);
    }

    #[Test]
    public function it_implements_payment_complete_processor_interface(): void
    {
        self::assertInstanceOf(PaymentCompleteProcessorInterface::class, $this->paypalPaymentCompleteProcessor);
    }

    #[Test]
    public function it_completes_payment_in_paypal(): void
    {
        $payment = $this->createMock(PaymentInterface::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $gateway = $this->createMock(GatewayInterface::class);

        $payment->method('getDetails')->willReturn(['paypal_order_id' => '123123']);
        $payment->method('getMethod')->willReturn($paymentMethod);
        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);
        $gatewayConfig->method('getGatewayName')->willReturn('paypal');

        $this->payum->method('getGateway')->with('paypal')->willReturn($gateway);

        $gateway->expects(self::once())
            ->method('execute')
            ->with($this->callback(function (CompleteOrder $request): bool {
                return $request->getOrderId() === '123123';
            }));

        $this->paypalPaymentCompleteProcessor->completePayment($payment);
    }

    #[Test]
    public function it_does_nothing_if_payment_has_no_paypal_order_id_set(): void
    {
        $payment = $this->createMock(PaymentInterface::class);

        $payment->method('getDetails')->willReturn([]);

        $this->payum->expects($this->never())->method('getGateway');

        $this->paypalPaymentCompleteProcessor->completePayment($payment);
    }
}
