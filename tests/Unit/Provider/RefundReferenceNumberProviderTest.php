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

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\PayPalPlugin\Provider\RefundReferenceNumberProvider;
use Sylius\PayPalPlugin\Provider\RefundReferenceNumberProviderInterface;

final class RefundReferenceNumberProviderTest extends TestCase
{
    private RefundReferenceNumberProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = new RefundReferenceNumberProvider();
    }

    #[Test]
    public function implements_refund_reference_number_provider_interface(): void
    {
        self::assertInstanceOf(RefundReferenceNumberProviderInterface::class, $this->provider);
    }

    #[Test]
    public function provides_reference_number_based_on_payment_id_and_current_date(): void
    {
        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getId')->willReturn(123);

        $result = $this->provider->provide($payment);

        $expectedFormat = '123-' . (new \DateTime())->format('d-m-Y');
        self::assertEquals($expectedFormat, $result);
    }
}
