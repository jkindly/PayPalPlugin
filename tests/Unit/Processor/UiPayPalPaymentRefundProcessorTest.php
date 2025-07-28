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

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Resource\Exception\UpdateHandlingException;
use Sylius\PayPalPlugin\Exception\PayPalOrderRefundException;
use Sylius\PayPalPlugin\Processor\PaymentRefundProcessorInterface;
use Sylius\PayPalPlugin\Processor\UiPayPalPaymentRefundProcessor;

final class UiPayPalPaymentRefundProcessorTest extends TestCase
{
    private UiPayPalPaymentRefundProcessor $uiPaypalPaymentRefundProcessor;

    private PaymentRefundProcessorInterface&MockObject $paymentRefundProcessor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentRefundProcessor = $this->createMock(PaymentRefundProcessorInterface::class);

        $this->uiPaypalPaymentRefundProcessor = new UiPayPalPaymentRefundProcessor($this->paymentRefundProcessor);
    }

    #[Test]
    public function it_implements_payment_refund_processor_interface(): void
    {
        self::assertInstanceOf(PaymentRefundProcessorInterface::class, $this->uiPaypalPaymentRefundProcessor);
    }

    #[Test]
    public function it_throws_exception_if_refund_has_fails(): void
    {
        $payment = $this->createMock(PaymentInterface::class);

        $this->paymentRefundProcessor
            ->method('refund')
            ->with($payment)
            ->willThrowException(new PayPalOrderRefundException());

        $this->expectException(UpdateHandlingException::class);

        $this->uiPaypalPaymentRefundProcessor->refund($payment);
    }

    #[Test]
    public function it_does_nothing_if_refund_was_successful(): void
    {
        $payment = $this->createMock(PaymentInterface::class);

        $this->paymentRefundProcessor->expects(self::once())->method('refund')->with($payment);

        $this->uiPaypalPaymentRefundProcessor->refund($payment);
    }
}
