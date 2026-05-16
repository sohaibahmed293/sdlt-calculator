<?php

return [

    /*
     * Standard residential rates (England), effective 1 April 2025.
     * Each band taxes only the slice of the price that falls within it.
     * Band boundaries are shared: band N's 'to' equals band N+1's 'from'.
     * A price exactly on a boundary falls in the lower band (break uses <=).
     * All thresholds are in pence to match internal arithmetic.
     */
    'standard_rates' => [
        ['from' => 0,          'to' => 12500000,  'rate' => 0.00],
        ['from' => 12500000,   'to' => 25000000,  'rate' => 0.02],
        ['from' => 25000000,   'to' => 92500000,  'rate' => 0.05],
        ['from' => 92500000,   'to' => 150000000, 'rate' => 0.10],
        ['from' => 150000000,  'to' => null,       'rate' => 0.12],
    ],

    /*
     * First-time buyer rates (England), effective 1 April 2025.
     * Only used when price <= first_time_buyer_max_price.
     * Above that cap, no partial relief applies; standard_rates are used in full.
     */
    'first_time_buyer_rates' => [
        ['from' => 0,         'to' => 30000000, 'rate' => 0.00],
        ['from' => 30000000,  'to' => 50000000, 'rate' => 0.05],
    ],

    // Above this price (in pence), first-time buyer relief does not apply at all.
    'first_time_buyer_max_price' => 50000000,

    /*
     * Additional property surcharge: a flat rate on the full purchase price.
     * Raised from 3% to 5% in the Autumn Budget, effective 31 October 2024.
     * Applies to every band including the nil-rate band, so an additional
     * property buyer pays 5% on the first £125,000 as well.
     */
    'additional_property_surcharge' => 0.05,

];
