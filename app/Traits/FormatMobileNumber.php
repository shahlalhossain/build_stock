<?php

namespace App\Traits;

trait FormatMobileNumber
{
    public function formatMobileNumber($mobileNumber) : ?string
    {
        if (!$mobileNumber) {
            return null;
        }

        // Take Last 11 Digits after Removing Non-Numeric Chars
        return substr(preg_replace('/\D/', '', $mobileNumber), -11);
    }
}
