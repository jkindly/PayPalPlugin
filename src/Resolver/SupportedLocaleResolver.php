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

namespace Sylius\PayPalPlugin\Resolver;

final class SupportedLocaleResolver implements SupportedLocaleResolverInterface
{
    private readonly array $supportedLocales;

    public function __construct(
        array $supportedLocales = [],
    ) {
        $this->supportedLocales = array_unique($supportedLocales);
    }

    public function resolve(string $locale): string
    {
        if (empty(trim($locale))) {
            throw new \UnexpectedValueException('Locale cannot be empty.');
        }

        $length = strlen($locale);
        if (5 === $length) {
            if (in_array($locale, $this->supportedLocales, true)) {
                return $locale;
            }

            throw new \UnexpectedValueException(
                sprintf('Locale "%s" is not supported by PayPal.', $locale),
            );
        }
        if (2 === $length) {
            return $this->findSupportedLocaleByLanguageCode($locale);
        }

        throw new \UnexpectedValueException(
            sprintf('Locale "%s" is not supported by PayPal.', $locale),
        );
    }

    private function findSupportedLocaleByLanguageCode(string $languageCode): string
    {
        foreach ($this->supportedLocales as $locale) {
            if (str_starts_with($locale, $languageCode)) {
                return $locale;
            }
        }

        throw new \UnexpectedValueException(
            sprintf('Language "%s" could not be resolved into a locale supported by PayPal.', $languageCode),
        );
    }
}
