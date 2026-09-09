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

namespace Sylius\PayPalPlugin\Form\Extension;

use Sylius\Bundle\AdminBundle\Form\Type\ShipmentShipType;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\PayPalPlugin\Manager\ShipmentTrackingManagerInterface;
use Sylius\PayPalPlugin\Provider\CarrierProviderInterface;
use Sylius\PayPalPlugin\Repository\ShipmentTrackingRepositoryInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final class ShipmentShipTypeExtension extends AbstractTypeExtension
{
    public function __construct(
        private readonly CarrierProviderInterface $carrierProvider,
        private readonly ShipmentTrackingRepositoryInterface $shipmentTrackingRepository,
        private readonly ShipmentTrackingManagerInterface $shipmentTrackingManager,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('carrier', ChoiceType::class, [
                'label' => 'sylius_paypal.form.shipment.carrier',
                'mapped' => false,
                'required' => false,
                'placeholder' => 'sylius_paypal.form.shipment.select_carrier',
                'choices' => array_flip($this->carrierProvider->getCarriers()),
            ])
            ->add('carrier_name_other', TextType::class, [
                'label' => 'sylius_paypal.form.shipment.carrier_name_other',
                'mapped' => false,
                'required' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'prefillCarrier']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'validateAndPersistCarrier'], -10);
    }

    public function prefillCarrier(FormEvent $event): void
    {
        $shipment = $event->getData();
        if (!$shipment instanceof ShipmentInterface) {
            return;
        }

        $tracking = $this->shipmentTrackingRepository->findOneByShipment($shipment);
        if ($tracking === null) {
            return;
        }

        $form = $event->getForm();
        $form->get('carrier')->setData($tracking->getCarrier());
        $form->get('carrier_name_other')->setData($tracking->getCarrierNameOther());
    }

    public function validateAndPersistCarrier(FormEvent $event): void
    {
        $form = $event->getForm();
        $shipment = $event->getData();
        if (!$shipment instanceof ShipmentInterface || !$form->isValid()) {
            return;
        }

        $trackingNumber = $shipment->getTracking();
        $carrier = $this->stringOrNull($form->get('carrier')->getData());
        $carrierNameOther = $this->stringOrNull($form->get('carrier_name_other')->getData());

        // A carrier is required whenever a tracking number is entered.
        if ($trackingNumber !== null && $trackingNumber !== '' && $carrier === null) {
            $form->get('carrier')->addError(new FormError('sylius_paypal.shipment_tracking.carrier_required'));

            return;
        }

        // The free-text carrier name is required when the "Other" carrier is chosen.
        if ($carrier !== null && $this->carrierProvider->isOther($carrier) && $carrierNameOther === null) {
            $form->get('carrier_name_other')->addError(new FormError('sylius_paypal.shipment_tracking.carrier_name_other_required'));

            return;
        }

        if ($carrier !== null) {
            $this->shipmentTrackingManager->updateCarrier($shipment, $carrier, $carrierNameOther);
        }
    }

    public static function getExtendedTypes(): iterable
    {
        return [ShipmentShipType::class];
    }

    private function stringOrNull(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
