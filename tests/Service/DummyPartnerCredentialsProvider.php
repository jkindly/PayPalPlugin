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

namespace Tests\Sylius\PayPalPlugin\Service;

use Sylius\PayPalPlugin\Model\PartnerCredentials;
use Sylius\PayPalPlugin\Provider\PartnerCredentialsProviderInterface;

final class DummyPartnerCredentialsProvider implements PartnerCredentialsProviderInterface
{
    public function provide(): PartnerCredentials
    {
        return new PartnerCredentials('PARTNER-ID', 'PARTNER-CLIENT-ID');
    }
}
