<?php

if (!function_exists('formatKm')) {
    function formatKm($km): string
    {
        if ($km === null) return '—';
        return number_format($km, 0, ',', '.') . ' km';
    }
}