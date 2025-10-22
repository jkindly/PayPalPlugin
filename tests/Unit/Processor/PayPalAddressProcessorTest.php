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

namespace Tests\Sylius\PayPalPlugin\Unit\Processor;

use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\PayPalPlugin\Processor\PayPalAddressProcessor;
use Sylius\PayPalPlugin\Processor\PayPalAddressProcessorInterface;

final class PayPalAddressProcessorTest extends TestCase
{
    private PayPalAddressProcessor $paypalAddressProcessor;

    private ObjectManager&MockObject $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = $this->createMock(ObjectManager::class);

        $this->paypalAddressProcessor = new PayPalAddressProcessor($this->objectManager);
    }

    #[Test]
    public function it_implements_paypal_address_processor_interface(): void
    {
        self::assertInstanceOf(PayPalAddressProcessorInterface::class, $this->paypalAddressProcessor);
    }

    #[Test]
    public function it_updates_order_address(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $orderAddress = $this->createMock(AddressInterface::class);

        $order->method('getShippingAddress')->willReturn($orderAddress);

        $orderAddress->expects(self::once())->method('setCity')->with('New York');
        $orderAddress->expects(self::once())->method('setStreet')->with('Main St. 123');
        $orderAddress->expects(self::once())->method('setPostcode')->with('10001');
        $orderAddress->expects(self::once())->method('setCountryCode')->with('US');

        $this->objectManager->expects(self::once())->method('flush');

        $this->paypalAddressProcessor->process(
            [
                'address_line_1' => 'Main St. 123',
                'admin_area_2' => 'New York',
                'postal_code' => '10001',
                'country_code' => 'US',
            ],
            $order,
        );
    }

    #[Test]
    public function it_updates_order_address_with_two_address_lines(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $orderAddress = $this->createMock(AddressInterface::class);

        $order->method('getShippingAddress')->willReturn($orderAddress);

        $orderAddress->expects(self::once())->method('setCity')->with('New York');
        $orderAddress->expects(self::once())->method('setStreet')->with('Main St. 123');
        $orderAddress->expects(self::once())->method('setPostcode')->with('10001');
        $orderAddress->expects(self::once())->method('setCountryCode')->with('US');

        $this->objectManager->expects(self::once())->method('flush');

        $this->paypalAddressProcessor->process(
            [
                'address_line_1' => 'Main St.',
                'address_line_2' => '123',
                'admin_area_2' => 'New York',
                'postal_code' => '10001',
                'country_code' => 'US',
            ],
            $order,
        );
    }

    #[Test]
    public function it_throws_an_exception_if_address_data_is_missing(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $orderAddress = $this->createMock(AddressInterface::class);

        $order->method('getShippingAddress')->willReturn($orderAddress);

        $this->objectManager->expects($this->never())->method('flush');

        $this->expectException(\InvalidArgumentException::class);

        $this->paypalAddressProcessor->process([], $order);
    }
}
