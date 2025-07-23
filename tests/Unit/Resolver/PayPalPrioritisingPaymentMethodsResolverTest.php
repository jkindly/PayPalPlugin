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

namespace Tests\Sylius\PayPalPlugin\Unit\Resolver;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Sylius\Component\Payment\Model\PaymentInterface as BasePaymentInterface;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;
use Sylius\PayPalPlugin\Resolver\PayPalPrioritisingPaymentMethodsResolver;

final class PayPalPrioritisingPaymentMethodsResolverTest extends TestCase
{
    private PaymentMethodsResolverInterface&MockObject $paymentMethodsResolver;
    private PayPalPrioritisingPaymentMethodsResolver $payPalPrioritisingPaymentMethodsResolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentMethodsResolver = $this->createMock(PaymentMethodsResolverInterface::class);

        $this->payPalPrioritisingPaymentMethodsResolver = new PayPalPrioritisingPaymentMethodsResolver(
            $this->paymentMethodsResolver,
            'prioritised'
        );
    }

    public function testItImplementsPaymentMethodsResolverInterface(): void
    {
        self::assertInstanceOf(PaymentMethodsResolverInterface::class, $this->payPalPrioritisingPaymentMethodsResolver);
    }

    public function testItPrioritizesPaymentMethod(): void
    {
        $payment = $this->createMock(BasePaymentInterface::class);
        $firstPayment = $this->createMock(PaymentMethodInterface::class);
        $secondPayment = $this->createMock(PaymentMethodInterface::class);
        $thirdPayment = $this->createMock(PaymentMethodInterface::class);
        $firstGatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $secondGatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $thirdGatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $firstPayment->method('getGatewayConfig')->willReturn($firstGatewayConfig);
        $firstGatewayConfig->method('getFactoryName')->willReturn('payment1');

        $secondPayment->method('getGatewayConfig')->willReturn($secondGatewayConfig);
        $secondGatewayConfig->method('getFactoryName')->willReturn('payment2');

        $thirdPayment->method('getGatewayConfig')->willReturn($thirdGatewayConfig);
        $thirdGatewayConfig->method('getFactoryName')->willReturn('prioritised');

        $this->paymentMethodsResolver->method('getSupportedMethods')->with($payment)->willReturn([$firstPayment, $secondPayment, $thirdPayment]);

        $result = $this->payPalPrioritisingPaymentMethodsResolver->getSupportedMethods($payment);

        self::assertSame([$thirdPayment, $firstPayment, $secondPayment], $result);
    }

    public function testItDoesNothingIfPrioritizedPaymentIsNotAvailable(): void
    {
        $payment = $this->createMock(BasePaymentInterface::class);
        $firstPayment = $this->createMock(PaymentMethodInterface::class);
        $secondPayment = $this->createMock(PaymentMethodInterface::class);
        $thirdPayment = $this->createMock(PaymentMethodInterface::class);
        $firstGatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $secondGatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $thirdGatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $firstPayment->method('getGatewayConfig')->willReturn($firstGatewayConfig);
        $firstGatewayConfig->method('getFactoryName')->willReturn('payment1');

        $secondPayment->method('getGatewayConfig')->willReturn($secondGatewayConfig);
        $secondGatewayConfig->method('getFactoryName')->willReturn('payment2');

        $thirdPayment->method('getGatewayConfig')->willReturn($thirdGatewayConfig);
        $thirdGatewayConfig->method('getFactoryName')->willReturn('payment3');

        $this->paymentMethodsResolver->method('getSupportedMethods')->with($payment)->willReturn([$firstPayment, $secondPayment, $thirdPayment]);

        $result = $this->payPalPrioritisingPaymentMethodsResolver->getSupportedMethods($payment);

        self::assertSame([$firstPayment, $secondPayment, $thirdPayment], $result);
    }
}
