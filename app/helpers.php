<?php

if (!function_exists('format_amount')) {
    function format_amount($n): string
    {
        $n = (float) $n;

        // If whole number
        if (floor($n) == $n) {
            return number_format($n, 0, '.', ',');
        }

        // Format with 2 decimals
        $s = number_format($n, 2, '.', ',');

        // Remove unnecessary zeros
        $s = rtrim(rtrim($s, '0'), '.');

        return $s === '' ? '0' : $s;
    }
}
