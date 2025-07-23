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
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Capture;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\PayPalPlugin\Payum\Action\StatusAction;

final class StatusActionTest extends TestCase
{
    private StatusAction $statusAction;

    protected function setUp(): void
    {
        $this->statusAction = new StatusAction();
    }

    public function testItImplementsActionInterface(): void
    {
        $this->assertInstanceOf(ActionInterface::class, $this->statusAction);
    }

    public function testItMarksRequestAsNew(): void
    {
        $request = $this->createMock(GetStatus::class);
        $payment = $this->createMock(PaymentInterface::class);

        $request->method('getFirstModel')->willReturn($payment);
        $request->method('getModel')->willReturn(['status' => StatusAction::STATUS_CREATED]);
        $request->expects($this->once())->method('markNew');

        $this->statusAction->execute($request);
    }

    public function testItMarksRequestAsPending(): void
    {
        $request = $this->createMock(GetStatus::class);
        $payment = $this->createMock(PaymentInterface::class);

        $request->method('getFirstModel')->willReturn($payment);
        $request->method('getModel')->willReturn(['status' => StatusAction::STATUS_CAPTURED]);
        $request->expects($this->once())->method('markPending');

        $this->statusAction->execute($request);
    }

    public function testItMarksRequestAsCaptured(): void
    {
        $request = $this->createMock(GetStatus::class);
        $payment = $this->createMock(PaymentInterface::class);

        $request->method('getFirstModel')->willReturn($payment);
        $request->method('getModel')->willReturn(['status' => 'COMPLETED']);
        $request->expects($this->once())->method('markCaptured');

        $this->statusAction->execute($request);
    }

    public function testItThrowsAnExceptionIfRequestIsNotSupported(): void
    {
        $request = $this->createMock(Capture::class);

        $this->expectException(RequestNotSupportedException::class);
        $this->statusAction->execute($request);
    }

    public function testItSupportsGetStatusRequestWithPaymentAsFirstModel(): void
    {
        $request = $this->createMock(GetStatus::class);
        $payment = $this->createMock(PaymentInterface::class);

        $request->method('getFirstModel')->willReturn($payment);

        $this->assertTrue($this->statusAction->supports($request));
    }

    public function testItDoesNotSupportRequestOtherThanGetStatus(): void
    {
        $request = $this->createMock(Capture::class);

        $this->assertFalse($this->statusAction->supports($request));
    }

    public function testItDoesNotSupportRequestWithFirstModelOtherThanPayment(): void
    {
        $request = $this->createMock(GetStatus::class);
        $request->method('getFirstModel')->willReturn('badObject');

        $this->assertFalse($this->statusAction->supports($request));
    }
}