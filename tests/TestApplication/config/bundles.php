<?php

declare(strict_types=1);

$bundles = [
    Sylius\PayPalPlugin\SyliusPayPalPlugin::class => ['all' => true],
    FOS\RestBundle\FOSRestBundle::class => ['all' => true],
];

if (class_exists('winzou\Bundle\StateMachineBundle\winzouStateMachineBundle')) {
    $bundles[winzou\Bundle\StateMachineBundle\winzouStateMachineBundle::class] = ['all' => true];
}

return $bundles;
