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

namespace Tests\Sylius\PayPalPlugin\Unit\Updater;

use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\PayPalPlugin\Updater\PaymentUpdaterInterface;
use Sylius\PayPalPlugin\Updater\PayPalPaymentUpdater;

final class PayPalPaymentUpdaterTest extends TestCase
{
    private ObjectManager&MockObject $paymentManager;

    private PayPalPaymentUpdater $payPalPaymentUpdater;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentManager = $this->createMock(ObjectManager::class);

        $this->payPalPaymentUpdater = new PayPalPaymentUpdater($this->paymentManager);
    }

    public function testItImplementsPaymentUpdaterInterface(): void
    {
        self::assertInstanceOf(PaymentUpdaterInterface::class, $this->payPalPaymentUpdater);
    }

    public function testItUpdatesPaymentAmount(): void
    {
        $payment = $this->createMock(PaymentInterface::class);

        $payment->expects(self::once())->method('setAmount')->with(1000);
        $this->paymentManager->expects(self::once())->method('flush');

        $this->payPalPaymentUpdater->updateAmount($payment, 1000);
    }
}
