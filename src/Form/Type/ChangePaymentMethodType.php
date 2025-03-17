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

namespace Sylius\PayPalPlugin\Form\Type;

use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

trigger_deprecation(
    'sylius/paypal-plugin',
    '1.7',
    'The "%s" class is deprecated and will be removed in Sylius/PayPalPlugin 2.0.',
    ChangePaymentMethodType::class,
);

/** @deprecated since Sylius/PayPalPlugin 1.7 and will be removed in Sylius/PayPalPlugin 2.0. */
final class ChangePaymentMethodType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event): void {
                /** @var PaymentInterface[] $payments */
                $payments = $event->getData();
                $form = $event->getForm();

                foreach ($payments as $key => $payment) {
                    if (!in_array(
                        $payment->getState(),
                        [PaymentInterface::STATE_NEW, PaymentInterface::STATE_CART, PaymentInterface::STATE_PROCESSING],
                    )) {
                        $form->remove((string) $key);
                    }
                }
            })
        ;
    }

    public function getParent(): string
    {
        return CollectionType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_change_payment_method';
    }
}
