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

namespace Tests\Sylius\PayPalPlugin\Unit\Provider;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\PayPalPlugin\Provider\RefundReferenceNumberProvider;
use Sylius\PayPalPlugin\Provider\RefundReferenceNumberProviderInterface;

final class RefundReferenceNumberProviderTest extends TestCase
{
    private RefundReferenceNumberProvider $provider;

    protected function setUp(): void
    {
        $this->provider = new RefundReferenceNumberProvider();
    }

    public function testImplementsRefundReferenceNumberProviderInterface(): void
    {
        $this->assertInstanceOf(RefundReferenceNumberProviderInterface::class, $this->provider);
    }

    public function testProvidesReferenceNumberBasedOnPaymentIdAndCurrentDate(): void
    {
        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getId')->willReturn(123);

        $result = $this->provider->provide($payment);

        $expectedFormat = '123-' . (new \DateTime())->format('d-m-Y');
        $this->assertEquals($expectedFormat, $result);
    }
}