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

namespace Tests\Sylius\PayPalPlugin\Unit;

use PHPUnit\Framework\TestCase;
use Sylius\PayPalPlugin\Payum\Action\StatusAction;
use Sylius\PayPalPlugin\Payum\Factory\PayPalGatewayFactory;

final class PayPalGatewayFactoryTest extends TestCase
{
    /** @test */
    public function it_populates_paypal_configuration(): void
    {
        $factory = new PayPalGatewayFactory();

        $config = $factory->createConfig();

        $this->assertEquals('PayPal', $config['payum.factory_title']);
        $this->assertEquals('paypal', $config['payum.factory_name']);
        $this->assertEquals(new StatusAction(), $config['payum.action.status']);
    }
}
