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

namespace Tests\Sylius\PayPalPlugin\Unit\Resolver;

use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Payum\Core\Request\Capture;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Sylius\PayPalPlugin\Resolver\CapturePaymentResolver;
use Sylius\PayPalPlugin\Resolver\CapturePaymentResolverInterface;

final class CapturePaymentResolverTest extends TestCase
{
    private Payum&MockObject $payum;
    private CapturePaymentResolver $capturePaymentResolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->payum = $this->createMock(Payum::class);

        $this->capturePaymentResolver = new CapturePaymentResolver($this->payum);
    }

    public function testItIsAnCapturePaymentResolver(): void
    {
        self::assertInstanceOf(CapturePaymentResolverInterface::class, $this->capturePaymentResolver);
    }

    public function testItExecutesCaptureActionOnPayment(): void
    {
        $payment = $this->createMock(PaymentInterface::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $gateway = $this->createMock(GatewayInterface::class);

        $payment->method('getMethod')->willReturn($paymentMethod);
        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);
        $gatewayConfig->method('getGatewayName')->willReturn('gateway-12');

        $this->payum->method('getGateway')->with('gateway-12')->willReturn($gateway);

        $gateway->expects(self::once())
            ->method('execute')
            ->with($this->callback(function (Capture $request) use ($payment): bool {
                return $request->getModel() === $payment;
            }));

        $this->capturePaymentResolver->resolve($payment);
    }
}
