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

namespace Tests\Sylius\PayPalPlugin\Api;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\PayPalPlugin\Api\UpdateOrderAddressApi;
use Sylius\PayPalPlugin\Client\PayPalClientInterface;

final class UpdateOrderAddressApiTest extends TestCase
{
    public function test_it_updates_order_address_in_paypal(): void
    {
        $client = $this->createMock(PayPalClientInterface::class);
        $shippingAddress = $this->createMock(AddressInterface::class);

        $api = new UpdateOrderAddressApi($client);

        $token = 'test_token';
        $orderId = '123456789';
        $referenceId = 'reference_123';

        $shippingAddress->method('getStreet')->willReturn('123 Main St');
        $shippingAddress->method('getCity')->willReturn('New York');
        $shippingAddress->method('getPostcode')->willReturn('10001');
        $shippingAddress->method('getCountryCode')->willReturn('US');
        $shippingAddress->method('getFullName')->willReturn('John Doe');

        $client->expects(self::exactly(2))
            ->method('patch')
            ->withConsecutive(
                [
                    "v2/checkout/orders/{$orderId}",
                    $token,
                    [[
                        'op' => 'replace',
                        'path' => "/purchase_units/@reference_id=='{$referenceId}'/shipping/address",
                        'value' => [
                            'address_line_1' => '123 Main St',
                            'admin_area_2' => 'New York',
                            'postal_code' => '10001',
                            'country_code' => 'US',
                        ],
                    ]],
                ],
                [
                    "v2/checkout/orders/{$orderId}",
                    $token,
                    [[
                        'op' => 'replace',
                        'path' => "/purchase_units/@reference_id=='{$referenceId}'/shipping/name",
                        'value' => ['full_name' => 'John Doe'],
                    ]],
                ],
            );

        $api->update($token, $orderId, $referenceId, $shippingAddress);
    }
}
