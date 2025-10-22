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
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Sylius\PayPalPlugin\Provider\PayPalConfigurationProvider;
use Sylius\PayPalPlugin\Provider\PayPalConfigurationProviderInterface;

final class PayPalConfigurationProviderTest extends TestCase
{
    /** @var PaymentMethodRepositoryInterface<PaymentMethodInterface>&MockObject */
    private PaymentMethodRepositoryInterface&MockObject $paymentMethodRepository;

    private PayPalConfigurationProvider $payPalConfigurationProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentMethodRepository = $this->createMock(PaymentMethodRepositoryInterface::class);
        $this->payPalConfigurationProvider = new PayPalConfigurationProvider($this->paymentMethodRepository);
    }

    #[Test]
    public function it_implements_pay_pal_configuration_provider_interface(): void
    {
        self::assertInstanceOf(PayPalConfigurationProviderInterface::class, $this->payPalConfigurationProvider);
    }

    #[Test]
    public function it_returns_client_id_from_payment_method_config(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $payPalPaymentMethod = $this->createMock(PaymentMethodInterface::class);
        $otherPaymentMethod = $this->createMock(PaymentMethodInterface::class);
        $payPalGatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $otherGatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $this->paymentMethodRepository
            ->method('findEnabledForChannel')
            ->with($channel)
            ->willReturn([$otherPaymentMethod, $payPalPaymentMethod]);

        $otherPaymentMethod
            ->method('getGatewayConfig')
            ->willReturn($otherGatewayConfig);

        $otherGatewayConfig
            ->method('getFactoryName')
            ->willReturn('other');

        $payPalPaymentMethod
            ->method('getGatewayConfig')
            ->willReturn($payPalGatewayConfig);

        $payPalGatewayConfig
            ->method('getFactoryName')
            ->willReturn('sylius_paypal');

        $payPalGatewayConfig
            ->method('getConfig')
            ->willReturn(['client_id' => '123123']);

        $result = $this->payPalConfigurationProvider->getClientId($channel);

        self::assertEquals('123123', $result);
    }

    #[Test]
    public function it_returns_partner_attribution_id_from_payment_method_config(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $payPalPaymentMethod = $this->createMock(PaymentMethodInterface::class);
        $otherPaymentMethod = $this->createMock(PaymentMethodInterface::class);
        $payPalGatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $otherGatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $this->paymentMethodRepository
            ->method('findEnabledForChannel')
            ->with($channel)
            ->willReturn([$otherPaymentMethod, $payPalPaymentMethod]);

        $otherPaymentMethod
            ->method('getGatewayConfig')
            ->willReturn($otherGatewayConfig);

        $otherGatewayConfig
            ->method('getFactoryName')
            ->willReturn('other');

        $payPalPaymentMethod
            ->method('getGatewayConfig')
            ->willReturn($payPalGatewayConfig);

        $payPalGatewayConfig
            ->method('getFactoryName')
            ->willReturn('sylius_paypal');

        $payPalGatewayConfig
            ->method('getConfig')
            ->willReturn(['partner_attribution_id' => '123123']);

        $result = $this->payPalConfigurationProvider->getPartnerAttributionId($channel);

        self::assertEquals('123123', $result);
    }

    #[Test]
    public function it_throws_an_exception_if_there_is_no_pay_pal_payment_method_defined(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $otherPaymentMethod = $this->createMock(PaymentMethodInterface::class);
        $otherGatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $this->paymentMethodRepository
            ->method('findEnabledForChannel')
            ->with($channel)
            ->willReturn([$otherPaymentMethod]);

        $otherPaymentMethod
            ->method('getGatewayConfig')
            ->willReturn($otherGatewayConfig);

        $otherGatewayConfig
            ->method('getFactoryName')
            ->willReturn('other');

        $this->expectException(\InvalidArgumentException::class);
        $this->payPalConfigurationProvider->getClientId($channel);
    }

    #[Test]
    public function it_throws_an_exception_if_there_is_no_pay_pal_payment_method_defined_for_partner_attribution_id(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $otherPaymentMethod = $this->createMock(PaymentMethodInterface::class);
        $otherGatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $this->paymentMethodRepository
            ->method('findEnabledForChannel')
            ->with($channel)
            ->willReturn([$otherPaymentMethod]);

        $otherPaymentMethod
            ->method('getGatewayConfig')
            ->willReturn($otherGatewayConfig);

        $otherGatewayConfig
            ->method('getFactoryName')
            ->willReturn('other');

        $this->expectException(\InvalidArgumentException::class);
        $this->payPalConfigurationProvider->getPartnerAttributionId($channel);
    }

    #[Test]
    public function it_throws_an_exception_if_there_is_no_client_id_on_pay_pal_payment_method(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $payPalPaymentMethod = $this->createMock(PaymentMethodInterface::class);
        $otherPaymentMethod = $this->createMock(PaymentMethodInterface::class);
        $payPalGatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $otherGatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $this->paymentMethodRepository
            ->method('findEnabledForChannel')
            ->with($channel)
            ->willReturn([$otherPaymentMethod, $payPalPaymentMethod]);

        $otherPaymentMethod
            ->method('getGatewayConfig')
            ->willReturn($otherGatewayConfig);

        $otherGatewayConfig
            ->method('getFactoryName')
            ->willReturn('other');

        $payPalPaymentMethod
            ->method('getGatewayConfig')
            ->willReturn($payPalGatewayConfig);

        $payPalGatewayConfig
            ->method('getFactoryName')
            ->willReturn('sylius_paypal');

        $payPalGatewayConfig
            ->method('getConfig')
            ->willReturn([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->payPalConfigurationProvider->getClientId($channel);
    }

    #[Test]
    public function it_throws_an_exception_if_there_is_no_partner_attribution_id_on_pay_pal_payment_method(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $payPalPaymentMethod = $this->createMock(PaymentMethodInterface::class);
        $otherPaymentMethod = $this->createMock(PaymentMethodInterface::class);
        $payPalGatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $otherGatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $this->paymentMethodRepository
            ->method('findEnabledForChannel')
            ->with($channel)
            ->willReturn([$otherPaymentMethod, $payPalPaymentMethod]);

        $otherPaymentMethod
            ->method('getGatewayConfig')
            ->willReturn($otherGatewayConfig);

        $otherGatewayConfig
            ->method('getFactoryName')
            ->willReturn('other');

        $payPalPaymentMethod
            ->method('getGatewayConfig')
            ->willReturn($payPalGatewayConfig);

        $payPalGatewayConfig
            ->method('getFactoryName')
            ->willReturn('sylius_paypal');

        $payPalGatewayConfig
            ->method('getConfig')
            ->willReturn([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->payPalConfigurationProvider->getPartnerAttributionId($channel);
    }
}
