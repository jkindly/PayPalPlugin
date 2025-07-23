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
    private PaymentMethodRepositoryInterface&MockObject $paymentMethodRepository;
    private PayPalConfigurationProvider $payPalConfigurationProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentMethodRepository = $this->createMock(PaymentMethodRepositoryInterface::class);
        $this->payPalConfigurationProvider = new PayPalConfigurationProvider($this->paymentMethodRepository);
    }

    public function testItImplementsPayPalConfigurationProviderInterface(): void
    {
        self::assertInstanceOf(PayPalConfigurationProviderInterface::class, $this->payPalConfigurationProvider);
    }

    public function testItReturnsClientIdFromPaymentMethodConfig(): void
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

    public function testItReturnsPartnerAttributionIdFromPaymentMethodConfig(): void
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

    public function testItThrowsAnExceptionIfThereIsNoPayPalPaymentMethodDefined(): void
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

    public function testItThrowsAnExceptionIfThereIsNoPayPalPaymentMethodDefinedForPartnerAttributionId(): void
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

    public function testItThrowsAnExceptionIfThereIsNoClientIdOnPayPalPaymentMethod(): void
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

    public function testItThrowsAnExceptionIfThereIsNoPartnerAttributionIdOnPayPalPaymentMethod(): void
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
