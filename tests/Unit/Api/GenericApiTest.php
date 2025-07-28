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

namespace Tests\Sylius\PayPalPlugin\Unit\Api;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Sylius\PayPalPlugin\Api\GenericApi;
use Sylius\PayPalPlugin\Api\GenericApiInterface;

final class GenericApiTest extends TestCase
{
    private ClientInterface&MockObject $client;

    private RequestFactoryInterface&MockObject $requestFactory;

    private GenericApi $genericApi;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createMock(ClientInterface::class);
        $this->requestFactory = $this->createMock(RequestFactoryInterface::class);
        $this->genericApi = new GenericApi($this->client, $this->requestFactory);
    }

    #[Test]
    public function it_implements_generic_api_interface(): void
    {
        self::assertInstanceOf(GenericApiInterface::class, $this->genericApi);
    }

    #[Test]
    public function it_calls_api_by_url(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $body = $this->createMock(StreamInterface::class);

        $this->requestFactory
            ->expects(self::once())
            ->method('createRequest')
            ->with('GET', 'http://url.com/')
            ->willReturn($request);

        $request
            ->expects($this->exactly(3))
            ->method('withHeader')
            ->willReturnCallback(function ($name, $value) use ($request) {
                $this->assertContains([$name, $value], [
                    ['Authorization', 'Bearer TOKEN'],
                    ['Content-Type', 'application/json'],
                    ['Accept', 'application/json'],
                ]);

                return $request;
            });

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
            ->willReturn('{ "parameter": "VALUE" }');

        $result = $this->genericApi->get('TOKEN', 'http://url.com/');

        self::assertEquals(['parameter' => 'VALUE'], $result);
    }
}
