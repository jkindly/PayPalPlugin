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

namespace Tests\Sylius\PayPalPlugin\Unit\Generator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Sylius\PayPalPlugin\Generator\PayPalAuthAssertionGenerator;
use Sylius\PayPalPlugin\Generator\PayPalAuthAssertionGeneratorInterface;

final class PayPalAuthAssertionGeneratorTest extends TestCase
{
    private PayPalAuthAssertionGenerator $payPalAuthAssertionGenerator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->payPalAuthAssertionGenerator = new PayPalAuthAssertionGenerator();
    }

    #[Test]
    public function it_implements_paypal_auth_assertion_generator_interface(): void
    {
        self::assertInstanceOf(PayPalAuthAssertionGeneratorInterface::class, $this->payPalAuthAssertionGenerator);
    }

    #[Test]
    public function it_generates_auth_assertion_based_on_payment_method_config(): void
    {
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $paymentMethod
            ->expects(self::once())
            ->method('getGatewayConfig')
            ->willReturn($gatewayConfig);

        $gatewayConfig
            ->expects(self::once())
            ->method('getConfig')
            ->willReturn(['client_id' => 'CLIENT_ID', 'merchant_id' => 'MERCHANT_ID']);

        $result = $this->payPalAuthAssertionGenerator->generate($paymentMethod);

        self::assertEquals('eyJhbGciOiJub25lIn0=.eyJpc3MiOiJDTElFTlRfSUQiLCJwYXllcl9pZCI6Ik1FUkNIQU5UX0lEIn0=.', $result);
    }

    #[Test]
    public function it_throws_an_exception_if_gateway_config_does_not_have_client_id(): void
    {
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $paymentMethod
            ->expects(self::once())
            ->method('getGatewayConfig')
            ->willReturn($gatewayConfig);

        $gatewayConfig
            ->expects(self::once())
            ->method('getConfig')
            ->willReturn(['merchant_id' => 'MERCHANT_ID']);

        $this->expectException(\InvalidArgumentException::class);

        $this->payPalAuthAssertionGenerator->generate($paymentMethod);
    }

    #[Test]
    public function it_throws_an_exception_if_gateway_config_does_not_have_merchant_id(): void
    {
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $paymentMethod
            ->expects(self::once())
            ->method('getGatewayConfig')
            ->willReturn($gatewayConfig);

        $gatewayConfig
            ->expects(self::once())
            ->method('getConfig')
            ->willReturn(['client_id' => 'CLIENT_ID']);

        $this->expectException(\InvalidArgumentException::class);

        $this->payPalAuthAssertionGenerator->generate($paymentMethod);
    }
}
