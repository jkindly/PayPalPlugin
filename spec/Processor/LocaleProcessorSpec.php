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

namespace spec\Sylius\PayPalPlugin\Processor;

use PhpSpec\ObjectBehavior;
use Sylius\PayPalPlugin\Resolver\SupportedLocaleResolverInterface;

final class LocaleProcessorSpec extends ObjectBehavior
{
    function let(SupportedLocaleResolverInterface $supportedLocaleResolver): void
    {
        $supportedLocaleResolver->resolve('et')->willReturn('et_EE');
        $supportedLocaleResolver->resolve('pl')->willReturn('pl_PL');
        $supportedLocaleResolver->resolve('ja')->willReturn('ja_JP');
        $supportedLocaleResolver->resolve('it_IT')->willReturn('it_IT');
        $supportedLocaleResolver->resolve('de_DE')->willReturn('de_DE');
        $supportedLocaleResolver->resolve('en')->willReturn('en_US');

        $this->beConstructedWith($supportedLocaleResolver);
    }

    function it_always_processes_locale_to_version_with_region(): void
    {
        $this->process('et')->shouldReturn('et_EE');
        $this->process('pl')->shouldReturn('pl_PL');
        $this->process('ja')->shouldReturn('ja_JP');
    }

    function it_returns_same_locale_if_it_is_valid(): void
    {
        $this->process('it_IT')->shouldReturn('it_IT');
        $this->process('de_DE')->shouldReturn('de_DE');
    }

    function it_returns_correct_locale_for_en_locale(): void
    {
        $this->process('en')->shouldReturn('en_US');
    }

    function it_always_processes_locale_to_version_with_region_using_legacy_mode(): void
    {
        $this->beConstructedWith(null);
        $this->process('et')->shouldReturn('et_EE');
        $this->process('pl')->shouldReturn('pl_PL');
        $this->process('ja')->shouldReturn('ja_JP');
    }

    function it_returns_same_locale_if_it_is_valid_using_legacy_mode(): void
    {
        $this->beConstructedWith(null);
        $this->process('it_IT')->shouldReturn('it_IT');
        $this->process('de_DE')->shouldReturn('de_DE');
        $this->process('ja_JP_TRADITIONAL')->shouldReturn('ja_JP_TRADITIONAL');
        $this->process('sd_Arab_PK')->shouldReturn('sd_Arab_PK');
    }

    function it_returns_correct_locale_for_en_locale_using_legacy_mode(): void
    {
        $this->beConstructedWith(null);
        $this->process('en')->shouldReturn('en_US');
    }
}
