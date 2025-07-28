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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Sylius\PayPalPlugin\Exception\PaymentNotFoundException;
use Sylius\PayPalPlugin\Provider\PaymentProvider;
use Sylius\PayPalPlugin\Provider\PaymentProviderInterface;

final class PaymentProviderTest extends TestCase
{
    /** @var PaymentRepositoryInterface<PaymentInterface>&MockObject */
    private PaymentRepositoryInterface&MockObject $paymentRepository;

    private PaymentProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentRepository = $this->createMock(PaymentRepositoryInterface::class);

        $this->provider = new PaymentProvider($this->paymentRepository);
    }

    #[Test]
    public function implements_payment_provider_interface(): void
    {
        self::assertInstanceOf(PaymentProviderInterface::class, $this->provider);
    }

    #[Test]
    public function returns_payment_for_given_paypal_order_id(): void
    {
        $firstPayment = $this->createMock(PaymentInterface::class);
        $secondPayment = $this->createMock(PaymentInterface::class);
        $thirdPayment = $this->createMock(PaymentInterface::class);

        $this->paymentRepository->method('findAll')->willReturn([$firstPayment, $secondPayment, $thirdPayment]);

        $firstPayment->method('getDetails')->willReturn(['test' => 'TEST']);
        $secondPayment->method('getDetails')->willReturn(['paypal_order_id' => 'PP123']);
        $thirdPayment->method('getDetails')->willReturn(['paypal_order_id' => 'PP444']);

        $result = $this->provider->getByPayPalOrderId('PP444');

        self::assertSame($thirdPayment, $result);
    }

    #[Test]
    public function throws_exception_if_there_is_no_payment_with_given_paypal_order_id(): void
    {
        $firstPayment = $this->createMock(PaymentInterface::class);
        $secondPayment = $this->createMock(PaymentInterface::class);
        $thirdPayment = $this->createMock(PaymentInterface::class);

        $this->paymentRepository->method('findAll')->willReturn([$firstPayment, $secondPayment, $thirdPayment]);

        $firstPayment->method('getDetails')->willReturn(['test' => 'TEST']);
        $secondPayment->method('getDetails')->willReturn(['paypal_order_id' => 'PP123']);
        $thirdPayment->method('getDetails')->willReturn(['paypal_order_id' => 'PP444']);

        $this->expectException(PaymentNotFoundException::class);
        $this->provider->getByPayPalOrderId('PP666');
    }
}
