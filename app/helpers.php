<?php

if (!function_exists('format_amount')) {
    /**
     * Format number for display: show exact (e.g. 10 not 10.00, 10.5 not 10.50 when appropriate).
     */
    function format_amount($n): string
    {
        $n = (float) $n;
        if (floor($n) == $n && $n == (int) $n) {
            return (string) (int) $n;
        }
        $s = number_format($n, 2, '.', '');
        $s = rtrim(rtrim($s, '0'), '.');
        return $s === '' ? '0' : $s;
    }
}
