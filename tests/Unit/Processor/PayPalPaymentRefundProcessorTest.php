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

use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Sylius\PayPalPlugin\Api\CacheAuthorizeClientApiInterface;
use Sylius\PayPalPlugin\Api\OrderDetailsApiInterface;
use Sylius\PayPalPlugin\Api\RefundPaymentApiInterface;
use Sylius\PayPalPlugin\Exception\PayPalOrderRefundException;
use Sylius\PayPalPlugin\Generator\PayPalAuthAssertionGeneratorInterface;
use Sylius\PayPalPlugin\Processor\PaymentRefundProcessorInterface;
use Sylius\PayPalPlugin\Processor\PayPalPaymentRefundProcessor;
use Sylius\PayPalPlugin\Provider\RefundReferenceNumberProviderInterface;

final class PayPalPaymentRefundProcessorTest extends TestCase
{
    private PayPalPaymentRefundProcessor $paypalPaymentRefundProcessor;

    private CacheAuthorizeClientApiInterface&MockObject $authorizeClientApi;

    private OrderDetailsApiInterface&MockObject $orderDetailsApi;

    private RefundPaymentApiInterface&MockObject $refundOrderApi;

    private PayPalAuthAssertionGeneratorInterface&MockObject $payPalAuthAssertionGenerator;

    private RefundReferenceNumberProviderInterface&MockObject $refundReferenceNumberProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authorizeClientApi = $this->createMock(CacheAuthorizeClientApiInterface::class);
        $this->orderDetailsApi = $this->createMock(OrderDetailsApiInterface::class);
        $this->refundOrderApi = $this->createMock(RefundPaymentApiInterface::class);
        $this->payPalAuthAssertionGenerator = $this->createMock(PayPalAuthAssertionGeneratorInterface::class);
        $this->refundReferenceNumberProvider = $this->createMock(RefundReferenceNumberProviderInterface::class);

        $this->paypalPaymentRefundProcessor = new PayPalPaymentRefundProcessor(
            $this->authorizeClientApi,
            $this->orderDetailsApi,
            $this->refundOrderApi,
            $this->payPalAuthAssertionGenerator,
            $this->refundReferenceNumberProvider,
        );
    }

    public function testItImplementsPaymentRefundProcessorInterface(): void
    {
        self::assertInstanceOf(PaymentRefundProcessorInterface::class, $this->paypalPaymentRefundProcessor);
    }

    public function testItFullyRefundsPaymentInPaypal(): void
    {
        $payment = $this->createMock(PaymentInterface::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $order = $this->createMock(OrderInterface::class);

        $payment->method('getMethod')->willReturn($paymentMethod);
        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);
        $gatewayConfig->method('getFactoryName')->willReturn('sylius_paypal');
        $payment->method('getDetails')->willReturn(['paypal_order_id' => '123123']);

        $this->authorizeClientApi->method('authorize')->with($paymentMethod)->willReturn('TOKEN');
        $this->orderDetailsApi
            ->method('get')
            ->with('TOKEN', '123123')
            ->willReturn(['purchase_units' => [['payments' => ['captures' => [['id' => '555', 'status' => 'COMPLETED']]]]]]);
        $this->payPalAuthAssertionGenerator->method('generate')->with($paymentMethod)->willReturn('AUTH-ASSERTION');

        $payment->method('getAmount')->willReturn(1000);
        $payment->method('getOrder')->willReturn($order);
        $order->method('getCurrencyCode')->willReturn('USD');

        $this->refundReferenceNumberProvider->method('provide')->with($payment)->willReturn('REFERENCE-NUMBER');

        $this->refundOrderApi
            ->expects(self::once())
            ->method('refund')
            ->with('TOKEN', '555', 'AUTH-ASSERTION', 'REFERENCE-NUMBER', '10', 'USD')
            ->willReturn(['status' => 'COMPLETED', 'id' => '123123']);

        // Test that refund method executes without throwing an exception
        $this->paypalPaymentRefundProcessor->refund($payment);

        // If we reach this point, the test passes
        $this->addToAssertionCount(1);
    }

    public function testItDoesNothingIfPaymentIsNotPaypal(): void
    {
        $payment = $this->createMock(PaymentInterface::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $payment->method('getMethod')->willReturn($paymentMethod);
        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);
        $gatewayConfig->method('getFactoryName')->willReturn('offline');
        $gatewayConfig->method('getConfig')->willReturn(['client_id' => 'CLIENT_ID', 'client_secret' => 'CLIENT_SECRET']);

        $this->refundOrderApi->expects($this->never())->method('refund');

        $this->paypalPaymentRefundProcessor->refund($payment);
    }

    public function testItDoesNothingIfPaymentIsPaymentHasNotPaypalOrderId(): void
    {
        $payment = $this->createMock(PaymentInterface::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $payment->method('getMethod')->willReturn($paymentMethod);
        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);
        $gatewayConfig->method('getFactoryName')->willReturn('sylius_paypal');
        $gatewayConfig->method('getConfig')->willReturn(['client_id' => 'CLIENT_ID', 'client_secret' => 'CLIENT_SECRET']);
        $payment->method('getDetails')->willReturn([]);

        $this->refundOrderApi->expects($this->never())->method('refund');

        $this->paypalPaymentRefundProcessor->refund($payment);
    }

    public function testItThrowsExceptionIfSomethingWentWrongDuringRefundingPayment(): void
    {
        $payment = $this->createMock(PaymentInterface::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $order = $this->createMock(OrderInterface::class);

        $payment->method('getMethod')->willReturn($paymentMethod);
        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);
        $gatewayConfig->method('getFactoryName')->willReturn('sylius_paypal');
        $payment->method('getDetails')->willReturn(['paypal_order_id' => '123123']);

        $this->authorizeClientApi->method('authorize')->with($paymentMethod)->willReturn('TOKEN');
        $this->orderDetailsApi
            ->method('get')
            ->with('TOKEN', '123123')
            ->willReturn(['purchase_units' => [['payments' => ['captures' => [['id' => '555', 'status' => 'COMPLETED']]]]]]);
        $this->payPalAuthAssertionGenerator->method('generate')->with($paymentMethod)->willReturn('AUTH-ASSERTION');

        $payment->method('getAmount')->willReturn(1000);
        $payment->method('getOrder')->willReturn($order);
        $order->method('getCurrencyCode')->willReturn('USD');

        $this->refundReferenceNumberProvider->method('provide')->with($payment)->willReturn('REFERENCE-NUMBER');

        $this->refundOrderApi
            ->method('refund')
            ->with('TOKEN', '555', 'AUTH-ASSERTION', 'REFERENCE-NUMBER', '10', 'USD')
            ->willThrowException($this->createMock(ClientException::class));

        $this->expectException(PayPalOrderRefundException::class);

        $this->paypalPaymentRefundProcessor->refund($payment);
    }
}
