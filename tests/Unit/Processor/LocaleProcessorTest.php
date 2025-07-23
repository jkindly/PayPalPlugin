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

use PHPUnit\Framework\TestCase;
use Sylius\PayPalPlugin\Processor\LocaleProcessor;

final class LocaleProcessorTest extends TestCase
{
    private LocaleProcessor $localeProcessor;

    protected function setUp(): void
    {
        $this->localeProcessor = new LocaleProcessor();
    }

    public function testItAlwaysProcessesLocaleToVersionWithRegion(): void
    {
        $this->assertEquals('et_EE', $this->localeProcessor->process('et'));
        $this->assertEquals('pl_PL', $this->localeProcessor->process('pl'));
        $this->assertEquals('ja_JP', $this->localeProcessor->process('ja'));
    }

    public function testItReturnsSameLocaleIfItIsValid(): void
    {
        $this->assertEquals('it_IT', $this->localeProcessor->process('it_IT'));
        $this->assertEquals('ja_JP_TRADITIONAL', $this->localeProcessor->process('ja_JP_TRADITIONAL'));
        $this->assertEquals('sd_Arab_PK', $this->localeProcessor->process('sd_Arab_PK'));
    }

    public function testItReturnsCorrectLocaleForEnLocale(): void
    {
        $this->assertEquals('en_US', $this->localeProcessor->process('en'));
    }
}