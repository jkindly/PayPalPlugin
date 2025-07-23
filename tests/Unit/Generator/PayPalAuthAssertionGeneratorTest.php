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

    public function testItImplementsPaypalAuthAssertionGeneratorInterface(): void
    {
        self::assertInstanceOf(PayPalAuthAssertionGeneratorInterface::class, $this->payPalAuthAssertionGenerator);
    }

    public function testItGeneratesAuthAssertionBasedOnPaymentMethodConfig(): void
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

    public function testItThrowsAnExceptionIfGatewayConfigDoesNotHaveClientId(): void
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

    public function testItThrowsAnExceptionIfGatewayConfigDoesNotHaveMerchantId(): void
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
