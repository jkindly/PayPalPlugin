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

namespace Sylius\PayPalPlugin\Twig;

use Sylius\PayPalPlugin\Form\Type\PayPalSandboxCredentialsType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class PayPalSandboxCredentialsFormExtension extends AbstractExtension
{
    public function __construct(private readonly FormFactoryInterface $formFactory)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('paypal_sandbox_form', [$this, 'createSandboxFormView']),
        ];
    }

    public function createSandboxFormView(): FormView
    {
        $form = $this->formFactory->create(PayPalSandboxCredentialsType::class);

        return $form->createView();
    }
}
