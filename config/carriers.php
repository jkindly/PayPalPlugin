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

/*
 * Curated list of carriers supported by the PayPal Add Tracking API, kept as an editable data
 * file rather than a class constant so it can be maintained without touching PHP classes.
 *
 * The keys are the exact carrier codes accepted by PayPal (purchase_units[].shipping.trackers[].carrier),
 * the values are human-readable labels shown in the admin carrier selector.
 *
 * For any carrier that is not listed here, use the OTHER code and provide the free-text carrier name
 * in the "carrier_name_other" field. The full enumeration (1000+ codes) is documented at
 * https://developer.paypal.com/docs/tracking/reference/carriers/ - extend this file as needed.
 */

return [
    // Global carriers
    'FEDEX' => 'FedEx',
    'UPS' => 'UPS',
    'USPS' => 'USPS',
    'DHL' => 'DHL',
    'DHL_EXPRESS' => 'DHL Express',
    'DHL_GLOBAL_MAIL' => 'DHL eCommerce',
    'DPD' => 'DPD',
    'DPD_LOCAL' => 'DPD Local',
    'GLS' => 'GLS',
    'TNT' => 'TNT',
    'ARAMEX' => 'Aramex',
    'AMAZON_MCF' => 'Amazon Logistics (MCF)',

    // Europe
    'DEUTSCHE_POST' => 'Deutsche Post',
    'DHL_DEUTSCHE_POST' => 'DHL (Deutsche Post)',
    'HERMES' => 'Hermes',
    'ROYAL_MAIL' => 'Royal Mail',
    'PARCELFORCE' => 'Parcelforce',
    'COLISSIMO' => 'Colissimo',
    'CHRONOPOST_FRANCE' => 'Chronopost France',
    'POCZTA_POLSKA' => 'Poczta Polska',
    'INPOST_PACZKOMATY' => 'InPost Paczkomaty',
    'DPD_POLAND' => 'DPD Poland',
    'CORREOS_SPAIN' => 'Correos (Spain)',
    'POSTNL' => 'PostNL',
    'BPOST' => 'bpost',
    'POSTNORD_SVERIGE' => 'PostNord Sweden',
    'POSTEN_NORGE' => 'Posten Norge',
    'AUSTRIAN_POST' => 'Austrian Post',
    'SWISS_POST' => 'Swiss Post',
    'POSTE_ITALIANE' => 'Poste Italiane',

    // North America
    'CANADA_POST' => 'Canada Post',
    'PUROLATOR' => 'Purolator',

    // Asia / Pacific
    'AUSTRALIA_POST' => 'Australia Post',
    'JAPAN_POST' => 'Japan Post',
    'CHINA_POST' => 'China Post',
    'SF_EXPRESS' => 'SF Express',
    'INDIA_POST' => 'India Post',

    // Fallback for any carrier not listed above; requires a free-text carrier name.
    'OTHER' => 'Other',
];
