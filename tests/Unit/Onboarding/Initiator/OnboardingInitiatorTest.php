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

namespace Tests\Sylius\PayPalPlugin\Unit\Onboarding\Initiator;

use Mockery\Mock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Sylius\PayPalPlugin\Onboarding\Initiator\OnboardingInitiator;
use Sylius\PayPalPlugin\Onboarding\Initiator\OnboardingInitiatorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class OnboardingInitiatorTest extends TestCase
{
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private Security&MockObject $security;
    private OnboardingInitiator $onboardingInitiator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->security = $this->createMock(Security::class);
        $this->onboardingInitiator = new OnboardingInitiator($this->urlGenerator, $this->security, 'https://paypal-url');
    }

    public function testItImplementsOnboardingInitiatorInterface(): void
    {
        self::assertInstanceOf(OnboardingInitiatorInterface::class, $this->onboardingInitiator);
    }

    public function testItThrowsAnExceptionDuringInitializationIfPaymentMethodIsNotSupported(): void
    {
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $paymentMethod->method('getGatewayConfig')->willReturn(null);

        $this->expectException(\DomainException::class);
        $this->onboardingInitiator->initiate($paymentMethod);
    }

    public function testItSupportsPaypalPaymentMethodWithoutClientIdSet(): void
    {
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);
        $gatewayConfig->method('getFactoryName')->willReturn('sylius_paypal');
        $gatewayConfig->method('getConfig')->willReturn(['some_parameter' => 'test']);

        self::assertTrue($this->onboardingInitiator->supports($paymentMethod));
    }

    public function testItDoesNotSupportPaypalPaymentMethodWithClientIdSet(): void
    {
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);
        $gatewayConfig->method('getFactoryName')->willReturn('sylius_paypal');
        $gatewayConfig->method('getConfig')->willReturn(['client_id' => '123123']);

        self::assertFalse($this->onboardingInitiator->supports($paymentMethod));
    }

    public function testItDoesNotSupportPaymentMethodWithInvalidGatewayFactoryName(): void
    {
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);
        $gatewayConfig->method('getFactoryName')->willReturn('offline');

        self::assertFalse($this->onboardingInitiator->supports($paymentMethod));
    }

    public function testItDoesNotSupportPaymentMethodWithoutGatewayConfig(): void
    {
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $paymentMethod->method('getGatewayConfig')->willReturn(null);

        self::assertFalse($this->onboardingInitiator->supports($paymentMethod));
    }

    public function testItReturnsUrlWhenPaymentIsValid(): void
    {
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $adminUser = $this->createMock(AdminUserInterface::class);

        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);
        $gatewayConfig->method('getFactoryName')->willReturn('sylius_paypal');
        $gatewayConfig->method('getConfig')->willReturn([]);

        $this->security->method('getUser')->willReturn($adminUser);
        $adminUser->method('getEmail')->willReturn('sylius@sylius.com');

        $this->urlGenerator
            ->method('generate')
            ->with('sylius_admin_payment_method_create', ['factory' => 'sylius_paypal'], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('/admin/payment-methods/new/sylius_paypal');

        $result = $this->onboardingInitiator->initiate($paymentMethod);

        self::assertEquals(
            'https://paypal-url/partner-referrals/create?email=sylius%40sylius.com&return_url=%2Fadmin%2Fpayment-methods%2Fnew%2Fsylius_paypal',
            $result
        );
    }
}
