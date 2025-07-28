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

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Sylius\Component\Payment\Exception\UnresolvedDefaultPaymentMethodException;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Sylius\Component\Payment\Resolver\DefaultPaymentMethodResolverInterface;
use Sylius\PayPalPlugin\Resolver\PayPalDefaultPaymentMethodResolver;

final class PayPalDefaultPaymentMethodResolverTest extends TestCase
{
    private DefaultPaymentMethodResolverInterface $decoratedDefaultPaymentMethodResolver;

    /** @var PaymentMethodRepositoryInterface<PaymentMethodInterface>&MockObject */
    private PaymentMethodRepositoryInterface&MockObject $paymentMethodRepository;

    private PayPalDefaultPaymentMethodResolver $payPalDefaultPaymentMethodResolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->decoratedDefaultPaymentMethodResolver = $this->createMock(DefaultPaymentMethodResolverInterface::class);
        $this->paymentMethodRepository = $this->createMock(PaymentMethodRepositoryInterface::class);

        $this->payPalDefaultPaymentMethodResolver = new PayPalDefaultPaymentMethodResolver(
            $this->decoratedDefaultPaymentMethodResolver,
            $this->paymentMethodRepository,
        );
    }

    #[Test]
    public function it_implements_default_payment_method_resolver_interface(): void
    {
        self::assertInstanceOf(DefaultPaymentMethodResolverInterface::class, $this->payPalDefaultPaymentMethodResolver);
    }

    #[Test]
    public function it_returns_prioritised_payment_method_for_channel(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $firstPayment = $this->createMock(PaymentMethodInterface::class);
        $secondPayment = $this->createMock(PaymentMethodInterface::class);
        $firstGatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $secondGatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $subject = $this->createMock(PaymentInterface::class);
        $order = $this->createMock(OrderInterface::class);

        $firstPayment->method('getGatewayConfig')->willReturn($firstGatewayConfig);
        $firstGatewayConfig->method('getFactoryName')->willReturn('new.payment');

        $secondPayment->method('getGatewayConfig')->willReturn($secondGatewayConfig);
        $secondGatewayConfig->method('getFactoryName')->willReturn('prioritised.payment');

        $this->paymentMethodRepository->method('findEnabledForChannel')->with($channel)->willReturn([$firstPayment, $secondPayment]);

        $subject->method('getOrder')->willReturn($order);
        $order->method('getChannel')->willReturn($channel);

        $result = $this->payPalDefaultPaymentMethodResolver->getDefaultPaymentMethod($subject, 'prioritised.payment');

        self::assertSame($secondPayment, $result);
    }

    #[Test]
    public function it_returns_first_available_payment_method_if_prioritised_payment_method_is_invalid(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $firstPayment = $this->createMock(PaymentMethodInterface::class);
        $secondPayment = $this->createMock(PaymentMethodInterface::class);
        $firstGatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $secondGatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $subject = $this->createMock(PaymentInterface::class);
        $order = $this->createMock(OrderInterface::class);

        $firstPayment->method('getGatewayConfig')->willReturn($firstGatewayConfig);
        $firstGatewayConfig->method('getFactoryName')->willReturn('payment1');

        $secondPayment->method('getGatewayConfig')->willReturn($secondGatewayConfig);
        $secondGatewayConfig->method('getFactoryName')->willReturn('payment2');

        $this->paymentMethodRepository->method('findEnabledForChannel')->with($channel)->willReturn([$firstPayment, $secondPayment]);

        $subject->method('getOrder')->willReturn($order);
        $order->method('getChannel')->willReturn($channel);

        $result = $this->payPalDefaultPaymentMethodResolver->getDefaultPaymentMethod($subject, 'prioritised');

        self::assertSame($firstPayment, $result);
    }

    #[Test]
    public function it_throws_error_if_there_is_no_available_payment(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $subject = $this->createMock(PaymentInterface::class);
        $order = $this->createMock(OrderInterface::class);

        $this->paymentMethodRepository->method('findEnabledForChannel')->with($channel)->willReturn([]);

        $subject->method('getOrder')->willReturn($order);
        $order->method('getChannel')->willReturn($channel);

        $this->expectException(UnresolvedDefaultPaymentMethodException::class);

        $this->payPalDefaultPaymentMethodResolver->getDefaultPaymentMethod($subject, 'prioritised');
    }
}
