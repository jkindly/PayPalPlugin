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

namespace Tests\Sylius\PayPalPlugin\Unit\Factory;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\ResourceBundle\Controller\NewResourceFactoryInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\PayPalPlugin\Factory\PayPalPaymentMethodNewResourceFactory;
use Sylius\PayPalPlugin\Onboarding\Processor\OnboardingProcessorInterface;
use Symfony\Component\HttpFoundation\Request;

final class PayPalPaymentMethodNewResourceFactoryTest extends TestCase
{
    private NewResourceFactoryInterface&MockObject $newResourceFactory;
    private OnboardingProcessorInterface&MockObject $onboardingProcessor;
    private PayPalPaymentMethodNewResourceFactory $payPalPaymentMethodNewResourceFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->newResourceFactory = $this->createMock(NewResourceFactoryInterface::class);
        $this->onboardingProcessor = $this->createMock(OnboardingProcessorInterface::class);

        $this->payPalPaymentMethodNewResourceFactory = new PayPalPaymentMethodNewResourceFactory(
            $this->newResourceFactory,
            $this->onboardingProcessor,
        );
    }

    public function testItIsANewResourceFactory(): void
    {
        self::assertInstanceOf(NewResourceFactoryInterface::class, $this->payPalPaymentMethodNewResourceFactory);
    }

    public function testItProcessesOnboardingIfPaymentMethodAndRequestAreSupported(): void
    {
        $requestConfiguration = $this->createMock(RequestConfiguration::class);
        $request = $this->createMock(Request::class);
        $factory = $this->createMock(FactoryInterface::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $processedPaymentMethod = $this->createMock(PaymentMethodInterface::class);

        $this->newResourceFactory
            ->expects(self::once())
            ->method('create')
            ->with($requestConfiguration, $factory)
            ->willReturn($paymentMethod);

        $requestConfiguration
            ->expects(self::once())
            ->method('getRequest')
            ->willReturn($request);

        $this->onboardingProcessor
            ->expects(self::once())
            ->method('supports')
            ->with($paymentMethod, $request)
            ->willReturn(true);

        $this->onboardingProcessor
            ->expects(self::once())
            ->method('process')
            ->with($paymentMethod, $request)
            ->willReturn($processedPaymentMethod);

        $result = $this->payPalPaymentMethodNewResourceFactory->create($requestConfiguration, $factory);

        self::assertEquals($processedPaymentMethod, $result);
    }

    public function testItDoesNothingIfPaymentMethodAndRequestAreUnsupported(): void
    {
        $requestConfiguration = $this->createMock(RequestConfiguration::class);
        $request = $this->createMock(Request::class);
        $factory = $this->createMock(FactoryInterface::class);
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);

        $this->newResourceFactory
            ->expects(self::once())
            ->method('create')
            ->with($requestConfiguration, $factory)
            ->willReturn($paymentMethod);

        $requestConfiguration
            ->expects(self::once())
            ->method('getRequest')
            ->willReturn($request);

        $this->onboardingProcessor
            ->expects(self::once())
            ->method('supports')
            ->with($paymentMethod, $request)
            ->willReturn(false);

        $this->onboardingProcessor
            ->expects($this->never())
            ->method('process');

        $result = $this->payPalPaymentMethodNewResourceFactory->create($requestConfiguration, $factory);

        self::assertEquals($paymentMethod, $result);
    }

    public function testItDoesNothingIfCreatedResourceIsNotAPaymentMethod(): void
    {
        $requestConfiguration = $this->createMock(RequestConfiguration::class);
        $factory = $this->createMock(FactoryInterface::class);
        $resource = $this->createMock(ResourceInterface::class);

        $this->newResourceFactory
            ->expects(self::once())
            ->method('create')
            ->with($requestConfiguration, $factory)
            ->willReturn($resource);

        $result = $this->payPalPaymentMethodNewResourceFactory->create($requestConfiguration, $factory);

        self::assertEquals($resource, $result);
    }
}
