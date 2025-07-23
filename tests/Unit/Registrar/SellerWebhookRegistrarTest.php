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

namespace Tests\Sylius\PayPalPlugin\Unit\Registrar;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Sylius\PayPalPlugin\Api\AuthorizeClientApiInterface;
use Sylius\PayPalPlugin\Api\WebhookApiInterface;
use Sylius\PayPalPlugin\Exception\PayPalWebhookUrlNotValidException;
use Sylius\PayPalPlugin\Registrar\SellerWebhookRegistrar;
use Sylius\PayPalPlugin\Registrar\SellerWebhookRegistrarInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SellerWebhookRegistrarTest extends TestCase
{
    private AuthorizeClientApiInterface&MockObject $authorizeClientApi;

    private UrlGeneratorInterface&MockObject $urlGenerator;

    private WebhookApiInterface&MockObject $webhookApi;

    private SellerWebhookRegistrar $sellerWebhookRegistrar;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authorizeClientApi = $this->createMock(AuthorizeClientApiInterface::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->webhookApi = $this->createMock(WebhookApiInterface::class);

        $this->sellerWebhookRegistrar = new SellerWebhookRegistrar(
            $this->authorizeClientApi,
            $this->urlGenerator,
            $this->webhookApi,
        );
    }

    public function testItImplementsSellerWebhookRegistrarInterface(): void
    {
        self::assertInstanceOf(SellerWebhookRegistrarInterface::class, $this->sellerWebhookRegistrar);
    }

    public function testItRegistersSellersWebhook(): void
    {
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);
        $gatewayConfig->method('getConfig')->willReturn(['client_id' => 'CLIENT_ID', 'client_secret' => 'CLIENT_SECRET']);

        $this->authorizeClientApi->method('authorize')->with('CLIENT_ID', 'CLIENT_SECRET')->willReturn('TOKEN');
        $this->urlGenerator
            ->method('generate')
            ->with('sylius_paypal_webhook_refund_order', [], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('https://webhook-url.com');

        $this->webhookApi
            ->expects(self::once())
            ->method('register')
            ->with('TOKEN', 'https://webhook-url.com')
            ->willReturn(['name' => 'WEBHOOK_REGISTERED']);

        // Test that register method executes without throwing an exception
        $this->sellerWebhookRegistrar->register($paymentMethod);

        // If we reach this point, the test passes
        $this->addToAssertionCount(1);
    }

    public function testItThrowsExceptionIfWebhookCouldNotBeRegistered(): void
    {
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $paymentMethod->method('getGatewayConfig')->willReturn($gatewayConfig);
        $gatewayConfig->method('getConfig')->willReturn(['client_id' => 'CLIENT_ID', 'client_secret' => 'CLIENT_SECRET']);

        $this->authorizeClientApi->method('authorize')->with('CLIENT_ID', 'CLIENT_SECRET')->willReturn('TOKEN');
        $this->urlGenerator
            ->method('generate')
            ->with('sylius_paypal_webhook_refund_order', [], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('https://webhook-url.com');

        $this->webhookApi->method('register')->with('TOKEN', 'https://webhook-url.com')->willReturn(['name' => 'VALIDATION_ERROR']);

        $this->expectException(PayPalWebhookUrlNotValidException::class);

        $this->sellerWebhookRegistrar->register($paymentMethod);
    }
}
