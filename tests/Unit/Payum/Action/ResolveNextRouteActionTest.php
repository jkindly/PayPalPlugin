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

namespace Tests\Sylius\PayPalPlugin\Unit\Payum\Action;

use Payum\Core\Request\Capture;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\PayumBundle\Request\ResolveNextRoute;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Sylius\PayPalPlugin\Payum\Action\ResolveNextRouteAction;

final class ResolveNextRouteActionTest extends TestCase
{
    private ResolveNextRouteAction $resolveNextRouteAction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolveNextRouteAction = new ResolveNextRouteAction();
    }

    public function testItExecutesResolveNextRouteRequestWithProcessingPayment(): void
    {
        $request = $this->createMock(ResolveNextRoute::class);
        $payment = $this->createMock(PaymentInterface::class);
        $order = $this->createMock(OrderInterface::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $request->method('getFirstModel')->willReturn($payment);

        $payment->method('getState')->willReturn(PaymentInterface::STATE_NEW);
        $payment->method('getId')->willReturn(12);
        $payment->method('getMethod')->willReturn($paymentMethod);
        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);
        $gatewayConfig->method('getFactoryName')->willReturn('sylius_paypal');

        $payment->method('getOrder')->willReturn($order);
        $order->method('getTokenValue')->willReturn('123!@#asd');

        $request->expects(self::once())->method('setRouteName')->with('sylius_paypal_shop_pay_with_paypal_form');
        $request->expects(self::once())->method('setRouteParameters')->with(['orderToken' => '123!@#asd', 'paymentId' => 12]);

        $this->resolveNextRouteAction->execute($request);
    }

    public function testItExecutesResolveNextRouteRequestWithCompletedPayment(): void
    {
        $request = $this->createMock(ResolveNextRoute::class);
        $payment = $this->createMock(PaymentInterface::class);
        $order = $this->createMock(OrderInterface::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $request->method('getFirstModel')->willReturn($payment);
        $payment->method('getOrder')->willReturn($order);

        $payment->method('getState')->willReturn(PaymentInterface::STATE_COMPLETED);
        $payment->method('getMethod')->willReturn($paymentMethod);
        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);
        $gatewayConfig->method('getFactoryName')->willReturn('sylius_paypal');

        $request->expects(self::once())->method('setRouteName')->with('sylius_shop_order_thank_you');

        $this->resolveNextRouteAction->execute($request);
    }

    public function testItExecutesResolveNextRouteRequestWithSomeOtherPayment(): void
    {
        $request = $this->createMock(ResolveNextRoute::class);
        $payment = $this->createMock(PaymentInterface::class);
        $order = $this->createMock(OrderInterface::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $request->method('getFirstModel')->willReturn($payment);

        $payment->method('getState')->willReturn(PaymentInterface::STATE_FAILED);
        $payment->method('getOrder')->willReturn($order);
        $payment->method('getMethod')->willReturn($paymentMethod);
        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);
        $gatewayConfig->method('getFactoryName')->willReturn('sylius_paypal');

        $order->method('getTokenValue')->willReturn('TOKEN_VALUE');

        $request->expects(self::once())->method('setRouteName')->with('sylius_shop_order_show');
        $request->expects(self::once())->method('setRouteParameters')->with(['tokenValue' => 'TOKEN_VALUE']);

        $this->resolveNextRouteAction->execute($request);
    }

    public function testItSupportsResolveNextRouteRequestWithPaymentAsFirstModel(): void
    {
        $request = $this->createMock(ResolveNextRoute::class);
        $payment = $this->createMock(PaymentInterface::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $request->method('getFirstModel')->willReturn($payment);
        $payment->method('getMethod')->willReturn($paymentMethod);
        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);
        $gatewayConfig->method('getFactoryName')->willReturn('sylius_paypal');

        self::assertTrue($this->resolveNextRouteAction->supports($request));
    }

    public function testItDoesNotSupportPaymentWithOtherFactoryNameThanPaypal(): void
    {
        $request = $this->createMock(ResolveNextRoute::class);
        $payment = $this->createMock(PaymentInterface::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $request->method('getFirstModel')->willReturn($payment);
        $payment->method('getMethod')->willReturn($paymentMethod);
        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);
        $gatewayConfig->method('getFactoryName')->willReturn('offline');

        self::assertFalse($this->resolveNextRouteAction->supports($request));
    }

    public function testItDoesNotSupportRequestOtherThanResolveNextRoute(): void
    {
        $request = $this->createMock(Capture::class);

        self::assertFalse($this->resolveNextRouteAction->supports($request));
    }

    public function testItDoesNotSupportRequestWithFirstModelOtherThanPayment(): void
    {
        $request = $this->createMock(ResolveNextRoute::class);
        $request->method('getFirstModel')->willReturn('badObject');

        self::assertFalse($this->resolveNextRouteAction->supports($request));
    }
}
