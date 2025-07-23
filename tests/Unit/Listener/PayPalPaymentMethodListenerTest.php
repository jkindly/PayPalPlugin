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

namespace Tests\Sylius\PayPalPlugin\Unit\Listener;

use PHPUnit\Framework\TestCase;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Sylius\PayPalPlugin\Exception\PayPalPaymentMethodNotFoundException;
use Sylius\PayPalPlugin\Listener\PayPalPaymentMethodListener;
use Sylius\PayPalPlugin\Onboarding\Initiator\OnboardingInitiatorInterface;
use Sylius\PayPalPlugin\Provider\PayPalPaymentMethodProviderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class PayPalPaymentMethodListenerTest extends TestCase
{
    private OnboardingInitiatorInterface $onboardingInitiator;
    private UrlGeneratorInterface $urlGenerator;
    private RequestStack $requestStack;
    private PayPalPaymentMethodProviderInterface $payPalPaymentMethodProvider;
    private PayPalPaymentMethodListener $payPalPaymentMethodListener;

    protected function setUp(): void
    {
        $this->onboardingInitiator = $this->createMock(OnboardingInitiatorInterface::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->payPalPaymentMethodProvider = $this->createMock(PayPalPaymentMethodProviderInterface::class);

        $this->payPalPaymentMethodListener = new PayPalPaymentMethodListener(
            $this->onboardingInitiator,
            $this->urlGenerator,
            $this->requestStack,
            $this->payPalPaymentMethodProvider,
        );
    }

    public function testItInitiatesOnboardingWhenCreatingASupportedPaymentMethod(): void
    {
        $event = $this->createMock(ResourceControllerEvent::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $event
            ->expects($this->once())
            ->method('getSubject')
            ->willReturn($paymentMethod);

        $paymentMethod
            ->expects($this->once())
            ->method('getGatewayConfig')
            ->willReturn($gatewayConfig);

        $gatewayConfig
            ->expects($this->once())
            ->method('getFactoryName')
            ->willReturn('sylius_paypal');

        $this->payPalPaymentMethodProvider
            ->expects($this->once())
            ->method('provide')
            ->willThrowException(new PayPalPaymentMethodNotFoundException());

        $this->onboardingInitiator
            ->expects($this->once())
            ->method('supports')
            ->with($paymentMethod)
            ->willReturn(true);

        $this->onboardingInitiator
            ->expects($this->once())
            ->method('initiate')
            ->with($paymentMethod)
            ->willReturn('https://example.com/onboarding-url');

        $event
            ->expects($this->once())
            ->method('setResponse')
            ->with($this->callback(function ($argument): bool {
                return $argument instanceof RedirectResponse && $argument->getTargetUrl() === 'https://example.com/onboarding-url';
            }));

        $this->payPalPaymentMethodListener->initializeCreate($event);
    }

    public function testItThrowsAnExceptionIfSubjectIsNotAPaymentMethod(): void
    {
        $event = $this->createMock(ResourceControllerEvent::class);

        $event
            ->expects($this->once())
            ->method('getSubject')
            ->willReturn(new \stdClass());

        $this->expectException(\InvalidArgumentException::class);

        $this->payPalPaymentMethodListener->initializeCreate($event);
    }

    public function testItRedirectsWithErrorIfThePaypalPaymentMethodAlreadyExists(): void
    {
        $event = $this->createMock(ResourceControllerEvent::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $session = $this->createMock(SessionInterface::class);
        $flashBag = $this->createMock(FlashBagInterface::class);

        $event
            ->expects($this->once())
            ->method('getSubject')
            ->willReturn($paymentMethod);

        $this->payPalPaymentMethodProvider
            ->expects($this->once())
            ->method('provide')
            ->willReturn($paymentMethod);

        $paymentMethod
            ->expects($this->once())
            ->method('getGatewayConfig')
            ->willReturn($gatewayConfig);

        $gatewayConfig
            ->expects($this->once())
            ->method('getFactoryName')
            ->willReturn('sylius_paypal');

        $flashBag
            ->expects($this->once())
            ->method('add')
            ->with('error', 'sylius_paypal.more_than_one_seller_not_allowed');

        $session
            ->expects($this->once())
            ->method('getBag')
            ->with('flashes')
            ->willReturn($flashBag);

        $this->requestStack
            ->expects($this->once())
            ->method('getSession')
            ->willReturn($session);

        $this->urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with('sylius_admin_payment_method_index')
            ->willReturn('http://redirect-url.com');

        $event
            ->expects($this->once())
            ->method('setResponse')
            ->with($this->callback(function (RedirectResponse $response): bool {
                return $response->getTargetUrl() === 'http://redirect-url.com';
            }));

        $this->onboardingInitiator
            ->expects($this->never())
            ->method('initiate');

        $this->payPalPaymentMethodListener->initializeCreate($event);
    }

    public function testItDoesNothingWhenCreatingAnUnsupportedPaymentMethod(): void
    {
        $event = $this->createMock(ResourceControllerEvent::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $event
            ->expects($this->once())
            ->method('getSubject')
            ->willReturn($paymentMethod);

        $paymentMethod
            ->expects($this->once())
            ->method('getGatewayConfig')
            ->willReturn($gatewayConfig);

        $gatewayConfig
            ->expects($this->once())
            ->method('getFactoryName')
            ->willReturn('sylius_paypal');

        $this->payPalPaymentMethodProvider
            ->expects($this->once())
            ->method('provide')
            ->willThrowException(new PayPalPaymentMethodNotFoundException());

        $this->onboardingInitiator
            ->expects($this->once())
            ->method('supports')
            ->with($paymentMethod)
            ->willReturn(false);

        $event
            ->expects($this->never())
            ->method('setResponse');

        $this->payPalPaymentMethodListener->initializeCreate($event);
    }

    public function testItDoesNothingIfPaymentMethodIsNotPaypal(): void
    {
        $event = $this->createMock(ResourceControllerEvent::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $event
            ->expects($this->once())
            ->method('getSubject')
            ->willReturn($paymentMethod);

        $paymentMethod
            ->expects($this->once())
            ->method('getGatewayConfig')
            ->willReturn($gatewayConfig);

        $gatewayConfig
            ->expects($this->once())
            ->method('getFactoryName')
            ->willReturn('offline');

        $event
            ->expects($this->never())
            ->method('setResponse');

        $this->payPalPaymentMethodListener->initializeCreate($event);
    }
}