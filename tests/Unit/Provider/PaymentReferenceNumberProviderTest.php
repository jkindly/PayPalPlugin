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
use Sylius\PayPalPlugin\Provider\PaymentReferenceNumberProvider;
use Sylius\PayPalPlugin\Provider\PaymentReferenceNumberProviderInterface;

final class PaymentReferenceNumberProviderTest extends TestCase
{
    private PaymentReferenceNumberProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = new PaymentReferenceNumberProvider();
    }

    #[Test]
    public function implements_payment_reference_number_provider_interface(): void
    {
        self::assertInstanceOf(PaymentReferenceNumberProviderInterface::class, $this->provider);
    }

    #[Test]
    public function provides_reference_number_based_on_payment_id_and_creation_date(): void
    {
        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getId')->willReturn(123);
        $payment->method('getCreatedAt')->willReturn(new \DateTime('10-03-2012'));

        $result = $this->provider->provide($payment);

        self::assertEquals('123-10-03-2012', $result);
    }
}
