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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\PayPalPlugin\Api\CompleteOrderApi;
use Sylius\PayPalPlugin\Api\CompleteOrderApiInterface;
use Sylius\PayPalPlugin\Client\PayPalClientInterface;

final class CompleteOrderApiTest extends TestCase
{
    private PayPalClientInterface&MockObject $client;

    private CompleteOrderApi $completeOrderApi;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createMock(PayPalClientInterface::class);
        $this->completeOrderApi = new CompleteOrderApi($this->client);
    }

    public function testItImplementsCompleteOrderApiInterface(): void
    {
        self::assertInstanceOf(CompleteOrderApiInterface::class, $this->completeOrderApi);
    }

    public function testItCompletesPaypalOrderWithGivenId(): void
    {
        $this->client
            ->expects(self::once())
            ->method('post')
            ->with('v2/checkout/orders/123123/capture', 'TOKEN')
            ->willReturn(['status' => 'COMPLETED', 'id' => 123]);

        $result = $this->completeOrderApi->complete('TOKEN', '123123');

        self::assertEquals(['status' => 'COMPLETED', 'id' => 123], $result);
    }
}
