<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class Format
{
    public static function rupiah(int|float $amount): string
    {
        $sign = $amount < 0 ? '-' : '';

        return $sign.'Rp '.number_format(abs($amount), 0, ',', '.');
    }

    public static function date(?string $date): string
    {
        return $date ? Carbon::parse($date)->format('j M Y') : '';
    }
}
