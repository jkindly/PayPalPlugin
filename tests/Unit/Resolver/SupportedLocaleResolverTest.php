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

namespace Tests\Sylius\PayPalPlugin\Unit\Resolver;

use PHPUnit\Framework\TestCase;
use Sylius\PayPalPlugin\Resolver\SupportedLocaleResolver;

final class SupportedLocaleResolverTest extends TestCase
{
    /** @dataProvider validFiveCharacterLocaleProvider */
    public function test_returns_exact_match_for_five_character_locale(string $locale, array $supportedLocales): void
    {
        $resolver = new SupportedLocaleResolver($supportedLocales);

        $result = $resolver->resolve($locale);

        $this->assertSame($locale, $result);
    }

    /** @return \Generator<string, array<string|array<string>>> */
    public static function validFiveCharacterLocaleProvider(): \Generator
    {
        yield 'en_US' => ['en_US', ['en_US', 'fr_FR', 'de_DE']];
        yield 'fr_FR' => ['fr_FR', ['en_US', 'fr_FR', 'de_DE']];
        yield 'de_DE' => ['de_DE', ['en_US', 'fr_FR', 'de_DE']];
    }

    /** @dataProvider unsupportedFiveCharacterLocaleProvider */
    public function test_throws_for_unsupported_five_character_locale(string $locale, array $supportedLocales): void
    {
        $resolver = new SupportedLocaleResolver($supportedLocales);

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage(sprintf('Locale "%s" is not supported by PayPal.', $locale));

        $resolver->resolve($locale);
    }

    /** @return \Generator<string, array<string|array<string>>> */
    public static function unsupportedFiveCharacterLocaleProvider(): \Generator
    {
        yield 'es_ES not in list' => ['es_ES', ['en_US', 'fr_FR', 'de_DE']];
        yield 'it_IT not in list' => ['it_IT', ['en_US', 'fr_FR']];
        yield 'pt_PT not in list' => ['pt_PT', ['en_GB', 'en_US']];
    }

    /** @dataProvider validLanguageCodeProvider */
    public function test_resolves_two_character_language_code_to_supported_locale(string $languageCode, array $supportedLocales, string $expectedLocale): void
    {
        $resolver = new SupportedLocaleResolver($supportedLocales);

        $result = $resolver->resolve($languageCode);

        $this->assertSame($expectedLocale, $result);
    }

    /** @return \Generator<string, array<string|array<string>>> */
    public static function validLanguageCodeProvider(): \Generator
    {
        yield 'en finds en_US' => ['en', ['en_US', 'en_GB', 'fr_FR', 'de_DE'], 'en_US'];
        yield 'de finds de_DE' => ['de', ['de_DE', 'de_AT', 'fr_FR'], 'de_DE'];
        yield 'fr finds fr_FR' => ['fr', ['en_US', 'fr_FR', 'de_DE'], 'fr_FR'];
    }

    /** @dataProvider unsupportedLanguageCodeProvider */
    public function test_throws_when_language_code_not_found(string $languageCode, array $supportedLocales): void
    {
        $resolver = new SupportedLocaleResolver($supportedLocales);

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage(sprintf('Language "%s" could not be resolved into a locale supported by PayPal.', $languageCode));

        $resolver->resolve($languageCode);
    }

    /** @return \Generator<string, array<string|array<string>>> */
    public static function unsupportedLanguageCodeProvider(): \Generator
    {
        yield 'es not in list' => ['es', ['en_US', 'fr_FR', 'de_DE']];
        yield 'it not in list' => ['it', ['en_US', 'fr_FR']];
        yield 'pt not in list' => ['pt', ['en_GB', 'en_US']];
    }

    /** @dataProvider invalidLocaleProvider */
    public function test_throws_for_invalid_locale_length(string $locale, array $supportedLocales): void
    {
        $resolver = new SupportedLocaleResolver($supportedLocales);

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage(sprintf('Locale "%s" is not supported by PayPal.', $locale));

        $resolver->resolve($locale);
    }

    /** @return \Generator<string, array<string|array<string>>> */
    public static function invalidLocaleProvider(): \Generator
    {
        yield 'three character locale' => ['eng', ['en_US', 'fr_FR']];
        yield 'six character locale' => ['en_US_X', ['en_US', 'fr_FR']];
        yield 'four character locale' => ['enUS', ['en_US', 'fr_FR']];
    }

    public function test_throws_for_empty_locale(): void
    {
        $resolver = new SupportedLocaleResolver(['en_US', 'fr_FR']);

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Locale cannot be empty.');

        $resolver->resolve('');
    }

    public function test_constructor_removes_duplicate_locales(): void
    {
        $supportedLocales = ['en_US', 'fr_FR', 'en_GB', 'de_DE', 'fr_FR'];
        $resolver = new SupportedLocaleResolver($supportedLocales);

        $result = $resolver->resolve('en');
        $this->assertSame('en_US', $result);
    }

    /** @dataProvider emptyLocaleProvider */
    public function test_throws_with_empty_locale(string $locale): void
    {
        $supportedLocales = ['en_US', 'fr_FR', 'en_US', 'de_DE', 'fr_FR'];
        $resolver = new SupportedLocaleResolver($supportedLocales);

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Locale cannot be empty.');

        $resolver->resolve($locale);
    }

    /** @return \Generator<string, array<array<string>>> */
    public static function emptyLocaleProvider(): \Generator
    {
        yield 'empty' => [''];
        yield 'space' => [' '];
    }
}
