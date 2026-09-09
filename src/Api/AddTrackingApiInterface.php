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

namespace Sylius\PayPalPlugin\Api;

interface AddTrackingApiInterface
{
    /**
     * Adds tracking information to a PayPal order (POST /v2/checkout/orders/{id}/track).
     *
     * The endpoint is idempotent on the (capture_id, tracking_number) pair, so repeating the same
     * call returns HTTP 200 with the existing tracker instead of creating a duplicate.
     *
     * @param array<string, mixed> $body
     *
     * @return array<string, mixed>
     */
    public function add(string $token, string $orderId, array $body): array;
}
