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

namespace Tests\Sylius\PayPalPlugin\Unit\Dispatcher;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\PayPalPlugin\Dispatcher\ShipmentTrackingDispatcher;
use Sylius\PayPalPlugin\Message\SendShipmentTracking;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class ShipmentTrackingDispatcherTest extends TestCase
{
    private MessageBusInterface&MockObject $messageBus;

    private ShipmentTrackingDispatcher $dispatcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->dispatcher = new ShipmentTrackingDispatcher($this->messageBus);
    }

    #[Test]
    public function it_dispatches_a_message_carrying_the_shipment_id(): void
    {
        $shipment = $this->createMock(ShipmentInterface::class);
        $shipment->method('getId')->willReturn(42);

        $this->messageBus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(
                fn (SendShipmentTracking $message): bool => $message->shipmentId === 42,
            ))
            ->willReturn(new Envelope(new SendShipmentTracking(42)));

        $this->dispatcher->dispatch($shipment);
    }

    #[Test]
    public function it_does_not_dispatch_when_the_shipment_has_no_id(): void
    {
        $shipment = $this->createMock(ShipmentInterface::class);
        $shipment->method('getId')->willReturn(null);

        $this->messageBus->expects(self::never())->method('dispatch');

        $this->dispatcher->dispatch($shipment);
    }
}
