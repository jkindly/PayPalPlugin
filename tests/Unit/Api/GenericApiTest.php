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
    private ClientInterface $client;
    private RequestFactoryInterface $requestFactory;
    private GenericApi $genericApi;

    protected function setUp(): void
    {
        $this->client = $this->createMock(ClientInterface::class);
        $this->requestFactory = $this->createMock(RequestFactoryInterface::class);
        $this->genericApi = new GenericApi($this->client, $this->requestFactory);
    }

    public function testItImplementsGenericApiInterface(): void
    {
        $this->assertInstanceOf(GenericApiInterface::class, $this->genericApi);
    }

    public function testItCallsApiByUrl(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $body = $this->createMock(StreamInterface::class);

        $this->requestFactory
            ->expects($this->once())
            ->method('createRequest')
            ->with('GET', 'http://url.com/')
            ->willReturn($request);

        $request
            ->expects($this->exactly(3))
            ->method('withHeader')
            ->withConsecutive(
                ['Authorization', 'Bearer TOKEN'],
                ['Content-Type', 'application/json'],
                ['Accept', 'application/json']
            )
            ->willReturn($request);

        $this->client
            ->expects($this->once())
            ->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $response
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($body);

        $body
            ->expects($this->once())
            ->method('getContents')
            ->willReturn('{ "parameter": "VALUE" }');

        $result = $this->genericApi->get('TOKEN', 'http://url.com/');

        $this->assertEquals(['parameter' => 'VALUE'], $result);
    }
}