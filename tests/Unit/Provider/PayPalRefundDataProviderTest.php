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

namespace Tests\Sylius\PayPalPlugin\Unit\Provider;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\PayPalPlugin\Api\CacheAuthorizeClientApiInterface;
use Sylius\PayPalPlugin\Api\GenericApiInterface;
use Sylius\PayPalPlugin\Exception\PayPalWrongDataException;
use Sylius\PayPalPlugin\Provider\PayPalPaymentMethodProviderInterface;
use Sylius\PayPalPlugin\Provider\PayPalRefundDataProvider;

final class PayPalRefundDataProviderTest extends TestCase
{
    private CacheAuthorizeClientApiInterface&MockObject $authorizeClientApi;
    private GenericApiInterface&MockObject $genericApi;
    private PayPalPaymentMethodProviderInterface&MockObject $payPalPaymentMethodProvider;
    private PayPalRefundDataProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authorizeClientApi = $this->createMock(CacheAuthorizeClientApiInterface::class);
        $this->genericApi = $this->createMock(GenericApiInterface::class);
        $this->payPalPaymentMethodProvider = $this->createMock(PayPalPaymentMethodProviderInterface::class);

        $this->provider = new PayPalRefundDataProvider(
            $this->authorizeClientApi,
            $this->genericApi,
            $this->payPalPaymentMethodProvider
        );
    }

    public function testProvidesDataFromProvidedUrl(): void
    {
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);

        $this->payPalPaymentMethodProvider->method('provide')->willReturn($paymentMethod);
        $this->authorizeClientApi->method('authorize')->with($paymentMethod)->willReturn('TOKEN');

        $this->genericApi->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                ['TOKEN', 'https://get-refund-data.com'],
                ['TOKEN', 'https://up.url.com']
            )
            ->willReturnOnConsecutiveCalls(
                [
                    'links' => [
                        ['rel' => 'self', 'href' => 'https://self.url.com'],
                        ['rel' => 'up', 'href' => 'https://up.url.com'],
                    ],
                ],
                ['data' => 'refund-data']
            );

        $this->provider->provide('https://get-refund-data.com');
    }

    public function testThrowsErrorIfPaypalDataDoesntContainUrl(): void
    {
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);

        $this->payPalPaymentMethodProvider->method('provide')->willReturn($paymentMethod);
        $this->authorizeClientApi->method('authorize')->with($paymentMethod)->willReturn('TOKEN');
        $this->genericApi->method('get')
            ->with('TOKEN', 'https://get-refund-data.com')
            ->willReturn([
                'links' => [
                    ['rel' => 'self', 'href' => 'https://self.url.com'],
                    ['rel' => 'get', 'href' => 'https://get.url.com'],
                ],
            ]);

        $this->expectException(PayPalWrongDataException::class);
        $this->provider->provide('https://get-refund-data.com');
    }
}
