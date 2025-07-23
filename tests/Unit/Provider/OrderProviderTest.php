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
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\PayPalPlugin\Exception\OrderNotFoundException;
use Sylius\PayPalPlugin\Provider\OrderProvider;
use Sylius\PayPalPlugin\Provider\OrderProviderInterface;

final class OrderProviderTest extends TestCase
{
    private OrderRepositoryInterface&MockObject $orderRepository;
    private OrderProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);

        $this->provider = new OrderProvider($this->orderRepository);
    }

    public function testIsAnOrderProvider(): void
    {
        self::assertInstanceOf(OrderProviderInterface::class, $this->provider);
    }

    public function testProvidesOrderByGivenId(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $this->orderRepository->method('find')->with(420)->willReturn($order);

        $result = $this->provider->provideOrderById(420);

        self::assertSame($order, $result);
    }

    public function testProvidesOrderByGivenToken(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $this->orderRepository->method('findOneByTokenValue')->with('token-str')->willReturn($order);

        $result = $this->provider->provideOrderByToken('token-str');

        self::assertSame($order, $result);
    }

    public function testThrowsErrorIfOrderIsNotFoundById(): void
    {
        $this->orderRepository->method('find')->with(123)->willReturn(null);

        $this->expectException(OrderNotFoundException::class);
        $this->provider->provideOrderById(123);
    }

    public function testThrowsErrorIfOrderIsNotFoundByToken(): void
    {
        $this->orderRepository->method('findOneByTokenValue')->with('token')->willReturn(null);

        $this->expectException(OrderNotFoundException::class);
        $this->provider->provideOrderByToken('token');
    }
}
