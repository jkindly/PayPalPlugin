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
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Capture;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\PayPalPlugin\Payum\Action\AuthorizeAction;
use Sylius\PayPalPlugin\Payum\Action\StatusAction;

final class AuthorizeActionTest extends TestCase
{
    private AuthorizeAction $authorizeAction;

    protected function setUp(): void
    {
        $this->authorizeAction = new AuthorizeAction();
    }

    public function testItImplementsActionInterface(): void
    {
        $this->assertInstanceOf(ActionInterface::class, $this->authorizeAction);
    }

    public function testItMarksPaymentAsCreated(): void
    {
        $request = $this->createMock(Authorize::class);
        $payment = $this->createMock(PaymentInterface::class);

        $request->method('getModel')->willReturn($payment);
        $payment->expects($this->once())->method('setDetails')->with(['status' => StatusAction::STATUS_CREATED]);

        $this->authorizeAction->execute($request);
    }

    public function testItThrowsAnExceptionIfRequestTypeIsInvalid(): void
    {
        $request = $this->createMock(GetStatus::class);

        $this->expectException(RequestNotSupportedException::class);
        $this->authorizeAction->execute($request);
    }

    public function testItSupportsAuthorizeRequestWithPaymentAsFirstModel(): void
    {
        $request = $this->createMock(Authorize::class);
        $payment = $this->createMock(PaymentInterface::class);

        $request->method('getModel')->willReturn($payment);

        $this->assertTrue($this->authorizeAction->supports($request));
    }

    public function testItDoesNotSupportRequestOtherThanAuthorize(): void
    {
        $request = $this->createMock(Capture::class);

        $this->assertFalse($this->authorizeAction->supports($request));
    }

    public function testItDoesNotSupportRequestWithFirstModelOtherThanPayment(): void
    {
        $request = $this->createMock(Authorize::class);
        $request->method('getModel')->willReturn('badObject');

        $this->assertFalse($this->authorizeAction->supports($request));
    }
}