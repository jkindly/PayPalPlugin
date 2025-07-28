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

namespace Tests\Sylius\PayPalPlugin\Unit\Payum\Request;

use Payum\Core\Request\Generic;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\PayPalPlugin\Payum\Request\CompleteOrder;

final class CompleteOrderTest extends TestCase
{
    private PaymentInterface $payment;

    private CompleteOrder $completeOrder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->payment = $this->createMock(PaymentInterface::class);
        $this->completeOrder = new CompleteOrder($this->payment, '123123');
    }

    #[Test]
    public function it_is_generic_action(): void
    {
        self::assertInstanceOf(Generic::class, $this->completeOrder);
    }

    #[Test]
    public function it_has_an_order_id(): void
    {
        self::assertEquals('123123', $this->completeOrder->getOrderId());
    }
}
