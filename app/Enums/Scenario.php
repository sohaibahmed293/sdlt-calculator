<?php

namespace App\Enums;

enum Scenario: string
{
    case Standard           = 'standard';
    case FirstTimeBuyer     = 'first_time_buyer';
    case AdditionalProperty = 'additional_property';
}
