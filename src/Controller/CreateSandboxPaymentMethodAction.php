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

namespace Sylius\PayPalPlugin\Controller;

use Psr\Log\LoggerInterface;
use Sylius\PayPalPlugin\Creator\PayPalSandboxPaymentMethodCreatorInterface;
use Sylius\PayPalPlugin\Form\Type\PayPalSandboxCredentialsType;
use Sylius\PayPalPlugin\Model\PayPalSandboxCredentials;
use Sylius\PayPalPlugin\Provider\FlashBagProvider;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\RouterInterface;

final readonly class CreateSandboxPaymentMethodAction
{
    private const INDEX_ROUTE = 'sylius_admin_payment_method_index';

    private const UPDATE_ROUTE = 'sylius_admin_payment_method_update';

    public function __construct(
        private FormFactoryInterface $formFactory,
        private PayPalSandboxPaymentMethodCreatorInterface $sandboxPaymentMethodCreator,
        private RouterInterface $router,
        private RequestStack $flashBagOrRequestStack,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $credentials = new PayPalSandboxCredentials();
        $form = $this->formFactory
            ->create(PayPalSandboxCredentialsType::class, $credentials)
            ->handleRequest($request)
        ;

        if (!$form->isSubmitted() || !$form->isValid()) {
            FlashBagProvider::getFlashBag($this->flashBagOrRequestStack)->add('error', 'sylius_paypal.invalid_paypal_sandbox_credentials');
            return new RedirectResponse(
                $this->router->generate(self::INDEX_ROUTE),
            );
        }

        $clientId = $credentials->getClientId();
        $clientSecret = $credentials->getClientSecret();
        $merchantId = $credentials->getMerchantId();

        try {
            $paymentMethod = $this->sandboxPaymentMethodCreator->create($clientId, $clientSecret, $merchantId);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());

            FlashBagProvider::getFlashBag($this->flashBagOrRequestStack)->add('error', 'sylius_paypal.could_not_create_paypal_payment_method');

            return new RedirectResponse(
                $this->router->generate(self::INDEX_ROUTE),
            );
        }

        return new RedirectResponse(
            $this->router->generate(
                self::UPDATE_ROUTE,
                ['id' => $paymentMethod->getId()],
            ),
        );
    }
}
