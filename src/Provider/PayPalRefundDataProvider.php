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

use Sylius\PayPalPlugin\Api\CacheAuthorizeClientApiInterface;
use Sylius\PayPalPlugin\Api\GenericApiInterface;
use Sylius\PayPalPlugin\Exception\PayPalWrongDataException;

final readonly class PayPalRefundDataProvider implements PayPalRefundDataProviderInterface
{
    public function __construct(
        private CacheAuthorizeClientApiInterface $authorizeClientApi,
        private GenericApiInterface $genericApi,
        private PayPalPaymentMethodProviderInterface $payPalPaymentMethodProvider,
    ) {
    }

    public function provide(string $refundRefundUrl): array
    {
        $paymentMethod = $this->payPalPaymentMethodProvider->provide();
        $token = $this->authorizeClientApi->authorize($paymentMethod);

        $refundData = $this->genericApi->get($token, $refundRefundUrl);

        /** @var string[] $link */
        foreach ($refundData['links'] as $link) {
            if ($link['rel'] === 'up') {
                return $this->genericApi->get($token, $link['href']);
            }
        }

        throw new PayPalWrongDataException();
    }
}
