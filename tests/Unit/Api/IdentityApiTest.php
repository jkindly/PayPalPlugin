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
use Sylius\PayPalPlugin\Api\IdentityApi;
use Sylius\PayPalPlugin\Api\IdentityApiInterface;
use Sylius\PayPalPlugin\Client\PayPalClientInterface;

final class IdentityApiTest extends TestCase
{
    private PayPalClientInterface $payPalClient;
    private IdentityApi $identityApi;

    protected function setUp(): void
    {
        $this->payPalClient = $this->createMock(PayPalClientInterface::class);
        $this->identityApi = new IdentityApi($this->payPalClient);
    }

    public function testItImplementsIdentityApiInterface(): void
    {
        $this->assertInstanceOf(IdentityApiInterface::class, $this->identityApi);
    }

    public function testItGeneratesIdentityToken(): void
    {
        $this->payPalClient
            ->expects($this->once())
            ->method('post')
            ->with('v1/identity/generate-token', 'TOKEN')
            ->willReturn(['client_token' => 'CLIENT-TOKEN']);

        $result = $this->identityApi->generateToken('TOKEN');

        $this->assertEquals('CLIENT-TOKEN', $result);
    }
}