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

use Payum\Core\Action\ActionInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Order\StateResolver\StateResolverInterface;
use Sylius\PayPalPlugin\Api\CacheAuthorizeClientApiInterface;
use Sylius\PayPalPlugin\Api\CompleteOrderApiInterface;
use Sylius\PayPalPlugin\Api\OrderDetailsApiInterface;
use Sylius\PayPalPlugin\Api\UpdateOrderAddressApiInterface;
use Sylius\PayPalPlugin\Api\UpdateOrderApiInterface;
use Sylius\PayPalPlugin\Payum\Action\CompleteOrderAction;
use Sylius\PayPalPlugin\Payum\Action\StatusAction;
use Sylius\PayPalPlugin\Payum\Request\CompleteOrder;
use Sylius\PayPalPlugin\Updater\PaymentUpdaterInterface;

final class CompleteOrderActionTest extends TestCase
{
    private CacheAuthorizeClientApiInterface&MockObject $authorizeClientApi;
    private UpdateOrderApiInterface&MockObject $updateOrderApi;
    private CompleteOrderApiInterface&MockObject $completeOrderApi;
    private OrderDetailsApiInterface&MockObject $orderDetailsApi;
    private PaymentUpdaterInterface $payPalPaymentUpdater;
    private StateResolverInterface $orderPaymentStateResolver;
    private CompleteOrderAction $completeOrderAction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authorizeClientApi = $this->createMock(CacheAuthorizeClientApiInterface::class);
        $this->updateOrderApi = $this->createMock(UpdateOrderApiInterface::class);
        $this->completeOrderApi = $this->createMock(CompleteOrderApiInterface::class);
        $this->orderDetailsApi = $this->createMock(OrderDetailsApiInterface::class);
        $this->payPalPaymentUpdater = $this->createMock(PaymentUpdaterInterface::class);
        $this->orderPaymentStateResolver = $this->createMock(StateResolverInterface::class);

        $this->completeOrderAction = new CompleteOrderAction(
            $this->authorizeClientApi,
            $this->updateOrderApi,
            $this->completeOrderApi,
            $this->orderDetailsApi,
            null,
            $this->payPalPaymentUpdater,
            $this->orderPaymentStateResolver,
            null
        );
    }

    public function testItImplementsActionInterface(): void
    {
        self::assertInstanceOf(ActionInterface::class, $this->completeOrderAction);
    }

    public function testItCompletesOrder(): void
    {
        $request = $this->createMock(CompleteOrder::class);
        $payment = $this->createMock(PaymentInterface::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $order = $this->createMock(OrderInterface::class);

        $request->method('getModel')->willReturn($payment);
        $payment->method('getMethod')->willReturn($paymentMethod);
        $payment->method('getDetails')->willReturn([]);
        $payment->method('getOrder')->willReturn($order);

        $this->authorizeClientApi->method('authorize')->with($paymentMethod)->willReturn('TOKEN');

        $request->method('getOrderId')->willReturn('123123');

        $payment->method('getAmount')->willReturn(1000);
        $order->method('getTotal')->willReturn(1000);

        $this->completeOrderApi->expects(self::once())->method('complete')->with('TOKEN', '123123');
        $this->orderDetailsApi->method('get')->with('TOKEN', '123123')->willReturn([
            'status' => 'COMPLETED',
            'id' => '123123',
            'purchase_units' => [
                ['reference_id' => 'REFERENCE_ID'],
            ],
        ]);

        $payment->expects(self::once())->method('setDetails')->with([
            'status' => StatusAction::STATUS_COMPLETED,
            'paypal_order_id' => '123123',
            'reference_id' => 'REFERENCE_ID',
        ]);

        $order->method('isShippingRequired')->willReturn(false);

        $this->completeOrderAction->execute($request);
    }

    public function testItCompletesOrderAndSavesTransactionId(): void
    {
        $request = $this->createMock(CompleteOrder::class);
        $payment = $this->createMock(PaymentInterface::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $order = $this->createMock(OrderInterface::class);

        $request->method('getModel')->willReturn($payment);
        $payment->method('getMethod')->willReturn($paymentMethod);
        $payment->method('getDetails')->willReturn([]);
        $payment->method('getOrder')->willReturn($order);

        $this->authorizeClientApi->method('authorize')->with($paymentMethod)->willReturn('TOKEN');

        $request->method('getOrderId')->willReturn('123123');

        $payment->method('getAmount')->willReturn(1000);
        $order->method('getTotal')->willReturn(1000);

        $this->completeOrderApi->expects(self::once())->method('complete')->with('TOKEN', '123123');
        $this->orderDetailsApi->method('get')->with('TOKEN', '123123')->willReturn([
            'status' => 'COMPLETED',
            'id' => '123123',
            'purchase_units' => [
                [
                    'reference_id' => 'REFERENCE_ID',
                    'payments' => ['captures' => [['id' => 'TRANSACTION_ID']]],
                ],
            ],
        ]);

        $payment->expects(self::once())->method('setDetails')->with([
            'status' => StatusAction::STATUS_COMPLETED,
            'paypal_order_id' => '123123',
            'reference_id' => 'REFERENCE_ID',
            'transaction_id' => 'TRANSACTION_ID',
        ]);

        $order->method('isShippingRequired')->willReturn(false);

        $this->completeOrderAction->execute($request);
    }

    public function testItUpdatesPaypalShippingAddressAndCompletesOrder(): void
    {
        $updateOrderAddressApi = $this->createMock(UpdateOrderAddressApiInterface::class);

        $completeOrderAction = new CompleteOrderAction(
            $this->authorizeClientApi,
            $this->updateOrderApi,
            $this->completeOrderApi,
            $this->orderDetailsApi,
            null,
            $this->payPalPaymentUpdater,
            $this->orderPaymentStateResolver,
            $updateOrderAddressApi
        );

        $request = $this->createMock(CompleteOrder::class);
        $payment = $this->createMock(PaymentInterface::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $order = $this->createMock(OrderInterface::class);
        $shippingAddress = $this->createMock(AddressInterface::class);

        $request->method('getModel')->willReturn($payment);
        $payment->method('getMethod')->willReturn($paymentMethod);
        $payment->method('getDetails')->willReturn([
            'paypal_order_id' => '123123',
            'reference_id' => 'REFERENCE_ID',
        ]);
        $payment->method('getOrder')->willReturn($order);

        $this->authorizeClientApi->method('authorize')->with($paymentMethod)->willReturn('TOKEN');

        $request->method('getOrderId')->willReturn('123123');

        $payment->method('getAmount')->willReturn(1000);
        $order->method('getTotal')->willReturn(1000);

        $this->completeOrderApi->expects(self::once())->method('complete')->with('TOKEN', '123123');
        $this->orderDetailsApi->method('get')->with('TOKEN', '123123')->willReturn([
            'status' => 'COMPLETED',
            'id' => '123123',
            'purchase_units' => [
                ['reference_id' => 'REFERENCE_ID'],
            ],
        ]);

        $payment->expects(self::once())->method('setDetails')->with([
            'status' => StatusAction::STATUS_COMPLETED,
            'paypal_order_id' => '123123',
            'reference_id' => 'REFERENCE_ID',
        ]);

        $order->method('isShippingRequired')->willReturn(true);
        $order->method('getShippingAddress')->willReturn($shippingAddress);

        $updateOrderAddressApi->expects(self::once())->method('update')->with('TOKEN', '123123', 'REFERENCE_ID', $shippingAddress);

        $completeOrderAction->execute($request);
    }
}
