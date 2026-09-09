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

namespace Sylius\PayPalPlugin\Provider;

final class CarrierProvider implements CarrierProviderInterface
{
    /** @var array<string, string>|null */
    private ?array $carriers = null;

    private readonly string $filePath;

    public function __construct(?string $filePath = null)
    {
        $this->filePath = $filePath ?? \dirname(__DIR__, 2) . '/config/carriers.php';
    }

    public function getCarriers(): array
    {
        if ($this->carriers === null) {
            /** @var array<string, string> $carriers */
            $carriers = require $this->filePath;

            $this->carriers = $carriers;
        }

        return $this->carriers;
    }

    public function isKnownCarrier(string $code): bool
    {
        return isset($this->getCarriers()[$code]);
    }

    public function isOther(string $code): bool
    {
        return $code === self::OTHER_CARRIER_CODE;
    }
}
