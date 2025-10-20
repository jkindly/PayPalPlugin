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

namespace Sylius\PayPalPlugin\Processor;

use Sylius\PayPalPlugin\Resolver\SupportedLocaleResolverInterface;
use Symfony\Component\Intl\Locales;

final class LocaleProcessor implements LocaleProcessorInterface
{
    private const ALLOWED_LOCALE_PATTERN = '/^[a-z]{2}(_[A-Z]{2})?$/';

    public function __construct(
        private readonly ?SupportedLocaleResolverInterface $supportedLocaleResolver = null,
    ) {
        if (null === $this->supportedLocaleResolver) {
            trigger_deprecation(
                'sylius/paypal-plugin',
                '1.7',
                sprintf(
                    'Not passing an instance of "%s" is deprecated and will be prohibited in 3.0',
                    SupportedLocaleResolverInterface::class,
                ),
            );
        }
    }

    public function process(string $locale): string
    {
        if (null !== $this->supportedLocaleResolver) {
            $locale = trim($locale);

            if ($this->isValidLocale($locale)) {
                return $this->supportedLocaleResolver->resolve($locale);
            }

            throw new \UnexpectedValueException(sprintf('Locale "%s" is not valid.', $locale));
        }

        return $this->legacyProcess($locale);
    }

    private function legacyProcess(string $locale): string
    {
        if (str_contains($locale, '_')) {
            return $locale;
        }

        if ($locale === 'en') {
            return 'en_US';
        }

        $locales = array_filter(Locales::getLocales(), function (string $targetLocale) use ($locale): bool {
            return
                str_starts_with($targetLocale, $locale) &&
                strlen($targetLocale) === 5 &&
                $this->isValidLocale($targetLocale)
            ;
        });

        if ([] === $locales) {
            throw new \UnexpectedValueException(sprintf('Locale "%s" is not supported by PayPal.', $locale));
        }

        return array_shift($locales);
    }

    private function isValidLocale(string $locale): bool
    {
        return
            false === str_contains($locale, ' ') &&
            1 === preg_match(self::ALLOWED_LOCALE_PATTERN, $locale)
        ;
    }
}
