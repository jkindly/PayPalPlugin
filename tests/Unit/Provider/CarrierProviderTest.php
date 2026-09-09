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

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Sylius\PayPalPlugin\Provider\CarrierProvider;

final class CarrierProviderTest extends TestCase
{
    private CarrierProvider $carrierProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->carrierProvider = new CarrierProvider();
    }

    #[Test]
    public function it_loads_carriers_from_the_data_file(): void
    {
        $carriers = $this->carrierProvider->getCarriers();

        self::assertArrayHasKey('FEDEX', $carriers);
        self::assertArrayHasKey('UPS', $carriers);
        self::assertArrayHasKey('OTHER', $carriers);
    }

    #[Test]
    public function it_recognises_known_carriers(): void
    {
        self::assertTrue($this->carrierProvider->isKnownCarrier('FEDEX'));
        self::assertFalse($this->carrierProvider->isKnownCarrier('NOT_A_REAL_CARRIER'));
    }

    #[Test]
    public function it_recognises_the_other_fallback_carrier(): void
    {
        self::assertTrue($this->carrierProvider->isOther('OTHER'));
        self::assertFalse($this->carrierProvider->isOther('FEDEX'));
    }
}
