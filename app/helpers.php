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

if (!function_exists('format_amount_readable')) {
    /**
     * Format number in human-readable form: 10 Million 9 Lakh 10K 322 (no Rs prefix).
     */
    function format_amount_readable($n): string
    {
        $n = (float) $n;
        $negative = $n < 0;
        $n = abs($n);
        $n = round($n, 2);
        if ($n < 1000) {
            return ($negative ? '−' : '') . (string) $n;
        }
        $parts = [];
        // Million (10^6)
        $millions = floor($n / 1_000_000);
        if ($millions > 0) {
            $parts[] = $millions . ' Million';
            $n -= $millions * 1_000_000;
        }
        // Lakh (10^5)
        $lakhs = floor($n / 1_00_000);
        if ($lakhs > 0) {
            $parts[] = $lakhs . ' Lakh';
            $n -= $lakhs * 1_00_000;
        }
        // K (thousands)
        $thousands = floor($n / 1000);
        if ($thousands > 0) {
            $parts[] = $thousands . 'K';
            $n -= $thousands * 1000;
        }
        // Units (remaining)
        if ($n >= 1 || empty($parts)) {
            $parts[] = (string) (int) round($n);
        }
        $out = implode(' ', $parts);
        if ($out === '') {
            return ($negative ? '−' : '') . '0';
        }
        return ($negative ? '−' : '') . $out;
    }
}
