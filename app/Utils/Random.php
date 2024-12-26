<?php

namespace App\Utils;

class Random
{
    function unique_code(int $number)
    {
        return strtoupper(
            str_replace(
                str_split('0123456789'),
                str_split('SQRTUVWXYZ'),
                base_convert($number + base_convert('10000', 26, 10), 10, 26)
            )
        );
    }
}
