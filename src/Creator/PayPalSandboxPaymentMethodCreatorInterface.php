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

namespace Sylius\PayPalPlugin\Creator;

use Sylius\Component\Core\Model\PaymentMethodInterface;

interface PayPalSandboxPaymentMethodCreatorInterface
{
    public const SYLIUS_SANDBOX_MERCHANT_ID = 'SYLIUS_SANDBOX_MERCHANT_ID';

    public const PARTNER_ATTRIBUTION_ID = 'sylius-ppcp4p-bn-code';

    public function create(string $clientId, string $clientSecret, string $merchantId): PaymentMethodInterface;
}
