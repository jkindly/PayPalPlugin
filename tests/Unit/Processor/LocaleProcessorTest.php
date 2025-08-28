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

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Sylius\PayPalPlugin\Processor\LocaleProcessor;

final class LocaleProcessorTest extends TestCase
{
    private LocaleProcessor $localeProcessor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->localeProcessor = new LocaleProcessor();
    }

    #[Test]
    public function it_always_processes_locale_to_version_with_region(): void
    {
        self::assertEquals('et_EE', $this->localeProcessor->process('et'));
        self::assertEquals('pl_PL', $this->localeProcessor->process('pl'));
        self::assertEquals('ja_JP', $this->localeProcessor->process('ja'));
    }

    #[Test]
    public function it_returns_same_locale_if_it_is_valid(): void
    {
        self::assertEquals('it_IT', $this->localeProcessor->process('it_IT'));
        self::assertEquals('ja_JP_TRADITIONAL', $this->localeProcessor->process('ja_JP_TRADITIONAL'));
        self::assertEquals('sd_Arab_PK', $this->localeProcessor->process('sd_Arab_PK'));
    }

    #[Test]
    public function it_returns_correct_locale_for_en_locale(): void
    {
        self::assertEquals('en_US', $this->localeProcessor->process('en'));
    }
}
