<?php

/**
 * Global Helper Functions
 * 
 * File ini berisi helper functions yang bisa digunakan di seluruh aplikasi
 * tanpa perlu import namespace.
 */

use App\Helpers\DateTimeHelper;

if (!function_exists('formatDateTime')) {
    /**
     * Format datetime dengan timezone WIB
     * 
     * @param mixed $timestamp
     * @param string $format
     * @return string
     */
    function formatDateTime($timestamp, string $format = 'd M Y H:i:s'): string
    {
        return DateTimeHelper::formatDateTime($timestamp, $format);
    }
}

if (!function_exists('formatDate')) {
    /**
     * Format date only
     * 
     * @param mixed $timestamp
     * @return string
     */
    function formatDate($timestamp): string
    {
        return DateTimeHelper::formatDate($timestamp);
    }
}

if (!function_exists('formatTime')) {
    /**
     * Format time only dengan WIB
     * 
     * @param mixed $timestamp
     * @return string
     */
    function formatTime($timestamp): string
    {
        return DateTimeHelper::formatTime($timestamp);
    }
}

if (!function_exists('formatRelative')) {
    /**
     * Format relative time (e.g., "5 minutes ago")
     * 
     * @param mixed $timestamp
     * @return string
     */
    function formatRelative($timestamp): string
    {
        return DateTimeHelper::formatRelative($timestamp);
    }
}

if (!function_exists('formatForExport')) {
    /**
     * Format untuk export CSV/Excel
     * 
     * @param mixed $timestamp
     * @return string
     */
    function formatForExport($timestamp): string
    {
        return DateTimeHelper::formatForExport($timestamp);
    }
}
