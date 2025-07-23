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

namespace Tests\Sylius\PayPalPlugin\Unit\Client;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\PayPalPlugin\Client\PayPalClient;
use Sylius\PayPalPlugin\Client\PayPalClientInterface;
use Sylius\PayPalPlugin\Exception\PayPalApiTimeoutException;
use Sylius\PayPalPlugin\Exception\PayPalAuthorizationException;
use Sylius\PayPalPlugin\Provider\PayPalConfigurationProviderInterface;
use Sylius\PayPalPlugin\Provider\UuidProviderInterface;

final class PayPalClientTest extends TestCase
{
    private ClientInterface&MockObject $client;
    private LoggerInterface&MockObject $logger;
    private UuidProviderInterface&MockObject $uuidProvider;
    private PayPalConfigurationProviderInterface&MockObject $payPalConfigurationProvider;
    private ChannelContextInterface&MockObject $channelContext;
    private RequestFactoryInterface&MockObject $requestFactory;
    private StreamFactoryInterface&MockObject $streamFactory;
    private PayPalClient $payPalClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createMock(ClientInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->uuidProvider = $this->createMock(UuidProviderInterface::class);
        $this->payPalConfigurationProvider = $this->createMock(PayPalConfigurationProviderInterface::class);
        $this->channelContext = $this->createMock(ChannelContextInterface::class);
        $this->requestFactory = $this->createMock(RequestFactoryInterface::class);
        $this->streamFactory = $this->createMock(StreamFactoryInterface::class);

        $channel = $this->createMock(ChannelInterface::class);
        $this->channelContext->method('getChannel')->willReturn($channel);

        $this->payPalClient = new PayPalClient(
            $this->client,
            $this->logger,
            $this->uuidProvider,
            $this->payPalConfigurationProvider,
            $this->channelContext,
            'https://test-api.paypal.com/',
            5,
            $this->requestFactory,
            $this->streamFactory,
            false,
        );
    }

    public function testItImplementsPaypalClientInterface(): void
    {
        self::assertInstanceOf(PayPalClientInterface::class, $this->payPalClient);
    }

    public function testItReturnsAuthTokenForGivenClientData(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $body = $this->createMock(StreamInterface::class);

        $this->requestFactory
            ->expects(self::once())
            ->method('createRequest')
            ->with('POST', 'https://test-api.paypal.com/v1/oauth2/token')
            ->willReturn($request);

        $request->method('withHeader')->willReturn($request);
        $request->method('withBody')->willReturn($request);

        $this->client
            ->expects(self::once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $response
            ->expects(self::once())
            ->method('getStatusCode')
            ->willReturn(200);

        $response
            ->expects(self::once())
            ->method('getBody')
            ->willReturn($body);

        $body
            ->expects(self::once())
            ->method('getContents')
            ->willReturn('{"access_token": "TOKEN"}');

        $result = $this->payPalClient->authorize('CLIENT_ID', 'CLIENT_SECRET');

        self::assertEquals(['access_token' => 'TOKEN'], $result);
    }

    public function testItThrowsAnExceptionIfClientCouldNotBeAuthorized(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $this->requestFactory
            ->expects(self::once())
            ->method('createRequest')
            ->with('POST', 'https://test-api.paypal.com/v1/oauth2/token')
            ->willReturn($request);

        $request->method('withHeader')->willReturn($request);
        $request->method('withBody')->willReturn($request);

        $this->client
            ->expects(self::once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $response
            ->expects(self::once())
            ->method('getStatusCode')
            ->willReturn(401);

        $this->expectException(PayPalAuthorizationException::class);

        $this->payPalClient->authorize('CLIENT_ID', 'CLIENT_SECRET');
    }

    public function testItCallsGetRequestOnPaypalApi(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $body = $this->createMock(StreamInterface::class);
        $channel = $this->createMock(ChannelInterface::class);

        $this->channelContext
            ->method('getChannel')
            ->willReturn($channel);

        $this->payPalConfigurationProvider
            ->expects(self::once())
            ->method('getPartnerAttributionId')
            ->with($channel)
            ->willReturn('TRACKING-ID');

        $this->requestFactory
            ->expects(self::once())
            ->method('createRequest')
            ->with('GET', 'https://test-api.paypal.com/v2/get-request/')
            ->willReturn($request);

        $request->method('withHeader')->willReturn($request);

        $this->client
            ->expects(self::once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $response
            ->expects(self::once())
            ->method('getStatusCode')
            ->willReturn(200);

        $response
            ->expects(self::once())
            ->method('getBody')
            ->willReturn($body);

        $body
            ->expects(self::once())
            ->method('getContents')
            ->willReturn('{"status": "OK", "id": "123123"}');

        $result = $this->payPalClient->get('v2/get-request/', 'TOKEN');

        self::assertEquals(['status' => 'OK', 'id' => '123123'], $result);
    }

    public function testItCallsPostRequestOnPaypalApi(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $body = $this->createMock(StreamInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $channel = $this->createMock(ChannelInterface::class);

        $this->channelContext
            ->method('getChannel')
            ->willReturn($channel);

        $this->uuidProvider
            ->expects(self::once())
            ->method('provide')
            ->willReturn('REQUEST-ID');

        $this->payPalConfigurationProvider
            ->expects(self::once())
            ->method('getPartnerAttributionId')
            ->with($channel)
            ->willReturn('TRACKING-ID');

        $this->requestFactory
            ->expects(self::once())
            ->method('createRequest')
            ->with('POST', 'https://test-api.paypal.com/v2/post-request/')
            ->willReturn($request);

        $this->streamFactory
            ->method('createStream')
            ->willReturn($stream);

        $request->method('withHeader')->willReturn($request);
        $request->method('withBody')->willReturn($request);

        $this->client
            ->expects(self::once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $response
            ->expects(self::once())
            ->method('getStatusCode')
            ->willReturn(200);

        $response
            ->expects(self::once())
            ->method('getBody')
            ->willReturn($body);

        $body
            ->expects(self::once())
            ->method('getContents')
            ->willReturn('{"status": "OK", "id": "123123"}');

        $result = $this->payPalClient->post('v2/post-request/', 'TOKEN', ['parameter' => 'value', 'another_parameter' => 'another_value']);

        self::assertEquals(['status' => 'OK', 'id' => '123123'], $result);
    }

    public function testItCallsPatchRequestOnPaypalApi(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $body = $this->createMock(StreamInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $channel = $this->createMock(ChannelInterface::class);

        $this->channelContext
            ->method('getChannel')
            ->willReturn($channel);

        $this->payPalConfigurationProvider
            ->expects(self::once())
            ->method('getPartnerAttributionId')
            ->with($channel)
            ->willReturn('TRACKING-ID');

        $this->requestFactory
            ->expects(self::once())
            ->method('createRequest')
            ->with('PATCH', 'https://test-api.paypal.com/v2/patch-request/123123')
            ->willReturn($request);

        $this->streamFactory
            ->method('createStream')
            ->willReturn($stream);

        $request->method('withHeader')->willReturn($request);
        $request->method('withBody')->willReturn($request);

        $this->client
            ->expects(self::once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $response
            ->expects(self::once())
            ->method('getStatusCode')
            ->willReturn(200);

        $response
            ->expects(self::once())
            ->method('getBody')
            ->willReturn($body);

        $body
            ->expects(self::once())
            ->method('getContents')
            ->willReturn('{"status": "OK", "id": "123123"}');

        $result = $this->payPalClient->patch('v2/patch-request/123123', 'TOKEN', ['parameter' => 'value', 'another_parameter' => 'another_value']);

        self::assertEquals(['status' => 'OK', 'id' => '123123'], $result);
    }

    public function testItThrowsExceptionIfTheTimeoutHasBeenReachedTheSpecifiedAmountOfTime(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $channel = $this->createMock(ChannelInterface::class);

        $this->channelContext
            ->method('getChannel')
            ->willReturn($channel);

        $this->payPalConfigurationProvider
            ->expects(self::once())
            ->method('getPartnerAttributionId')
            ->with($channel)
            ->willReturn('TRACKING-ID');

        $this->requestFactory
            ->method('createRequest')
            ->with('GET', 'https://test-api.paypal.com/v2/get-request/')
            ->willReturn($request);

        $request->method('withHeader')->willReturn($request);

        $this->client
            ->method('sendRequest')
            ->with($request)
            ->willThrowException(new ConnectException('Connection timeout', $request));

        $this->expectException(PayPalApiTimeoutException::class);

        $this->payPalClient->get('v2/get-request/', 'TOKEN');
    }
}
