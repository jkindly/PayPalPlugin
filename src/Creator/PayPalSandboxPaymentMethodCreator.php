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

namespace Sylius\PayPalPlugin\Creator;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final readonly class PayPalSandboxPaymentMethodCreator implements PayPalSandboxPaymentMethodCreatorInterface
{
    public function __construct(
        private FactoryInterface $gatewayFactory,
        private FactoryInterface $paymentMethodFactory,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function create(string $clientId, string $clientSecret, string $merchantId): PaymentMethodInterface
    {
        $gatewayConfig = $this->createGatewayConfig($clientId, $clientSecret, $merchantId);
        $paymentMethod = $this->createPaymentMethod($gatewayConfig);

        $this->entityManager->persist($paymentMethod);
        $this->entityManager->flush();

        return $paymentMethod;
    }

    private function createGatewayConfig(string $clientId, string $clientSecret, string $merchantId): GatewayConfigInterface
    {
        /** @var GatewayConfigInterface $gatewayConfig */
        $gatewayConfig = $this->gatewayFactory->createNew();
        $gatewayConfig->setFactoryName('sylius_paypal');
        $gatewayConfig->setGatewayName('paypal_sandbox');

        $gatewayConfig->setConfig([
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'merchant_id' => $merchantId,
            'use_authorize' => 1,
            'sylius_merchant_id' => self::SYLIUS_SANDBOX_MERCHANT_ID,
            'reports_sftp_password' => null,
            'reports_sftp_username' => null,
            'partner_attribution_id' => self::PARTNER_ATTRIBUTION_ID,
        ]);

        return $gatewayConfig;
    }

    private function createPaymentMethod(GatewayConfigInterface $gatewayConfig): PaymentMethodInterface
    {
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $this->paymentMethodFactory->createNew();
        $paymentMethod->setGatewayConfig($gatewayConfig);
        $paymentMethod->setCode('PAYPAL');
        $paymentMethod->setName('PayPal');
        $paymentMethod->setDescription('Pay with PayPal');

        return $paymentMethod;
    }
}
