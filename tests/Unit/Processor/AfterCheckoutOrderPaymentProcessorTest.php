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
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\PayPalPlugin\Processor\AfterCheckoutOrderPaymentProcessor;

final class AfterCheckoutOrderPaymentProcessorTest extends TestCase
{
    private OrderProcessorInterface&MockObject $baseOrderPaymentProcessor;

    private AfterCheckoutOrderPaymentProcessor $afterCheckoutOrderPaymentProcessor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->baseOrderPaymentProcessor = $this->createMock(OrderProcessorInterface::class);
        $this->afterCheckoutOrderPaymentProcessor = new AfterCheckoutOrderPaymentProcessor($this->baseOrderPaymentProcessor);
    }

    public function testItImplementsOrderProcessorInterface(): void
    {
        self::assertInstanceOf(OrderProcessorInterface::class, $this->afterCheckoutOrderPaymentProcessor);
    }

    public function testItDoesNothingIfOrderIsNotCompleted(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $order->method('getCheckoutState')->willReturn(OrderCheckoutStates::STATE_ADDRESSED);

        $this->baseOrderPaymentProcessor->expects($this->never())->method('process');

        $this->afterCheckoutOrderPaymentProcessor->process($order);
    }

    public function testItUsesProcessorIfOrderIsCompleted(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $order->method('getCheckoutState')->willReturn(OrderCheckoutStates::STATE_COMPLETED);

        $this->baseOrderPaymentProcessor->expects(self::once())->method('process')->with($order);

        $this->afterCheckoutOrderPaymentProcessor->process($order);
    }
}
