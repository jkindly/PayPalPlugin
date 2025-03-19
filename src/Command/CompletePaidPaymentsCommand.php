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

namespace Sylius\PayPalPlugin\Command;

trigger_deprecation(
    'sylius/paypal-plugin',
    '1.7',
    'The "%s" class is deprecated and will be removed in Sylius/PayPalPlugin 2.0. Use "%s" instead.',
    CompletePaidPaymentsCommand::class,
    \Sylius\PayPalPlugin\Console\Command\CompletePaidPaymentsCommand::class,
);

class_exists(\Sylius\PayPalPlugin\Console\Command\CompletePaidPaymentsCommand::class);

if (false) {
    final class CompletePaidPaymentsCommand
    {
    }
}
