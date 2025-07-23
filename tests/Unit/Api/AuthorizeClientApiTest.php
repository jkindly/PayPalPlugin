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
use Sylius\PayPalPlugin\Api\AuthorizeClientApi;
use Sylius\PayPalPlugin\Api\AuthorizeClientApiInterface;
use Sylius\PayPalPlugin\Client\PayPalClientInterface;

final class AuthorizeClientApiTest extends TestCase
{
    private PayPalClientInterface $payPalClient;
    private AuthorizeClientApi $authorizeClientApi;

    protected function setUp(): void
    {
        $this->payPalClient = $this->createMock(PayPalClientInterface::class);
        $this->authorizeClientApi = new AuthorizeClientApi($this->payPalClient);
    }

    public function testItImplementsAuthorizeClientApiInterface(): void
    {
        $this->assertInstanceOf(AuthorizeClientApiInterface::class, $this->authorizeClientApi);
    }

    public function testItReturnsAuthTokenForGivenClientData(): void
    {
        $this->payPalClient
            ->method('authorize')
            ->with('CLIENT_ID', 'CLIENT_SECRET')
            ->willReturn(['access_token' => 'TOKEN']);

        $result = $this->authorizeClientApi->authorize('CLIENT_ID', 'CLIENT_SECRET');

        $this->assertEquals('TOKEN', $result);
    }
}