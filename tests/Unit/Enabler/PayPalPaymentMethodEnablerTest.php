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

namespace Tests\Sylius\PayPalPlugin\Unit\Enabler;

use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Sylius\PayPalPlugin\Enabler\PaymentMethodEnablerInterface;
use Sylius\PayPalPlugin\Enabler\PayPalPaymentMethodEnabler;
use Sylius\PayPalPlugin\Exception\PaymentMethodCouldNotBeEnabledException;
use Sylius\PayPalPlugin\Registrar\SellerWebhookRegistrarInterface;

final class PayPalPaymentMethodEnablerTest extends TestCase
{
    private ClientInterface&MockObject $client;
    private RequestFactoryInterface&MockObject $requestFactory;
    private ObjectManager&MockObject $paymentMethodManager;
    private SellerWebhookRegistrarInterface&MockObject $sellerWebhookRegistrar;
    private PayPalPaymentMethodEnabler $payPalPaymentMethodEnabler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createMock(ClientInterface::class);
        $this->requestFactory = $this->createMock(RequestFactoryInterface::class);
        $this->paymentMethodManager = $this->createMock(ObjectManager::class);
        $this->sellerWebhookRegistrar = $this->createMock(SellerWebhookRegistrarInterface::class);

        $this->payPalPaymentMethodEnabler = new PayPalPaymentMethodEnabler(
            $this->client,
            'http://base-url.com',
            $this->paymentMethodManager,
            $this->sellerWebhookRegistrar,
            $this->requestFactory,
        );
    }

    public function testItImplementsPaymentMethodEnablerInterface(): void
    {
        self::assertInstanceOf(PaymentMethodEnablerInterface::class, $this->payPalPaymentMethodEnabler);
    }

    public function testItEnablesPaymentMethodIfItHasProperCredentialsAndWebhookAreSet(): void
    {
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $body = $this->createMock(StreamInterface::class);

        $paymentMethod
            ->expects(self::once())
            ->method('getGatewayConfig')
            ->willReturn($gatewayConfig);

        $gatewayConfig
            ->expects(self::once())
            ->method('getConfig')
            ->willReturn(['merchant_id' => '123123', 'client_id' => 'CLIENT-ID', 'client_secret' => 'SECRET']);

        $this->requestFactory
            ->expects(self::once())
            ->method('createRequest')
            ->with('GET', 'http://base-url.com/seller-permissions/check/123123')
            ->willReturn($request);

        $this->client
            ->expects(self::once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $response
            ->expects(self::once())
            ->method('getBody')
            ->willReturn($body);

        $body
            ->expects(self::once())
            ->method('getContents')
            ->willReturn('{ "permissionsGranted": true }');

        $this->sellerWebhookRegistrar
            ->expects(self::once())
            ->method('register')
            ->with($paymentMethod);

        $paymentMethod
            ->expects(self::once())
            ->method('setEnabled')
            ->with(true);

        $this->paymentMethodManager
            ->expects(self::once())
            ->method('flush');

        $this->payPalPaymentMethodEnabler->enable($paymentMethod);
    }

    public function testItThrowsExceptionIfPaymentMethodCredentialsAreNotGranted(): void
    {
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $body = $this->createMock(StreamInterface::class);

        $paymentMethod
            ->expects(self::once())
            ->method('getGatewayConfig')
            ->willReturn($gatewayConfig);

        $gatewayConfig
            ->expects(self::once())
            ->method('getConfig')
            ->willReturn(['merchant_id' => '123123', 'client_id' => 'CLIENT-ID', 'client_secret' => 'SECRET']);

        $this->requestFactory
            ->expects(self::once())
            ->method('createRequest')
            ->with('GET', 'http://base-url.com/seller-permissions/check/123123')
            ->willReturn($request);

        $this->client
            ->expects(self::once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $response
            ->expects(self::once())
            ->method('getBody')
            ->willReturn($body);

        $body
            ->expects(self::once())
            ->method('getContents')
            ->willReturn('{ "permissionsGranted": false }');

        $this->sellerWebhookRegistrar
            ->expects($this->never())
            ->method('register');

        $paymentMethod
            ->expects($this->never())
            ->method('setEnabled');

        $this->paymentMethodManager
            ->expects($this->never())
            ->method('flush');

        $this->expectException(PaymentMethodCouldNotBeEnabledException::class);

        $this->payPalPaymentMethodEnabler->enable($paymentMethod);
    }
}
