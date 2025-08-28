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
use Sylius\PayPalPlugin\Api\OrderDetailsApi;
use Sylius\PayPalPlugin\Api\OrderDetailsApiInterface;
use Sylius\PayPalPlugin\Client\PayPalClientInterface;

final class OrderDetailsApiTest extends TestCase
{
    private PayPalClientInterface&MockObject $client;

    private OrderDetailsApi $orderDetailsApi;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createMock(PayPalClientInterface::class);
        $this->orderDetailsApi = new OrderDetailsApi($this->client);
    }

    #[Test]
    public function it_implements_paypal_order_details_provider_interface(): void
    {
        self::assertInstanceOf(OrderDetailsApiInterface::class, $this->orderDetailsApi);
    }

    #[Test]
    public function it_provides_details_about_paypal_order(): void
    {
        $this->client
            ->expects(self::once())
            ->method('get')
            ->with('v2/checkout/orders/123123', 'TOKEN')
            ->willReturn(['total' => 1111]);

        $result = $this->orderDetailsApi->get('TOKEN', '123123');

        self::assertEquals(['total' => 1111], $result);
    }
}
