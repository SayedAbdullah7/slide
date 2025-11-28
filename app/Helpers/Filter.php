<?php

namespace App\Helpers;

use Carbon\Carbon;

/**
 * Filter Helper - Reusable filter definitions for DataTables
 *
 * Usage in DataTable:
 * public function filters(): array {
 *     return [
 *         'status' => Filter::select('Status', ['1' => 'Active', '0' => 'Inactive']),
 *         'created_at' => Filter::date('Created Date'),
 *         'email' => Filter::text('Email'),
 *         'amount' => Filter::number('Amount'),
 *         'price' => Filter::range('Price Range', 0, 1000000),
 *     ];
 * }
 *
 * That's it! Filters are automatically applied on backend and rendered on frontend.
 */
class Filter
{

    /**
     * Generate filter data for a select dropdown.
     *
     * @param string $label
     * @param array $options Key-value pairs
     * @param string|null $column Database column name (defaults to filter key)
     * @return array
     */
    public static function select(string $label, array $options, ?string $column = null): array
    {
        return [
            'type' => 'select',
            'label' => $label,
            'options' => $options,
            'column' => $column,
        ];
    }

    /**
     * Generate filter data for a date input.
     *
     * @param string $label
     * @param string|null $max Maximum date ('today' or date string)
     * @param string|null $min Minimum date ('today' or date string)
     * @param string|null $column Database column name (defaults to filter key)
     * @return array
     */
    public static function date(string $label, ?string $max = null, ?string $min = null, ?string $column = null): array
    {
        if ($min == 'today') {
            $min = Carbon::today()->toDateString();
        }
        if ($max == 'today') {
            $max = Carbon::today()->toDateString();
        }
        return [
            'type' => 'date',
            'label' => $label,
            'min' => $min,
            'max' => $max,
            'column' => $column,
        ];
    }

    /**
     * Generate filter data for a date range (from - to).
     *
     * @param string $label
     * @param string|null $max Maximum date ('today' or date string)
     * @param string|null $min Minimum date ('today' or date string)
     * @param string|null $column Database column name (defaults to filter key)
     * @return array
     */
    public static function dateRange(string $label, ?string $max = null, ?string $min = null, ?string $column = null): array
    {
        if ($min == 'today') {
            $min = Carbon::today()->toDateString();
        }
        if ($max == 'today') {
            $max = Carbon::today()->toDateString();
        }
        return [
            'type' => 'date-range',
            'label' => $label,
            'min' => $min,
            'max' => $max,
            'column' => $column,
        ];
    }

    /**
     * Generate filter data for a text input.
     *
     * @param string $label
     * @param string|null $placeholder
     * @param string|null $column Database column name (defaults to filter key)
     * @return array
     */
    public static function text(string $label, ?string $placeholder = null, ?string $column = null): array
    {
        return [
            'type' => 'text',
            'label' => $label,
            'placeholder' => $placeholder ?? $label,
            'column' => $column,
        ];
    }

    /**
     * Generate filter data for a number input.
     *
     * @param string $label
     * @param float|null $min Minimum value
     * @param float|null $max Maximum value
     * @param string|null $column Database column name (defaults to filter key)
     * @return array
     */
    public static function number(string $label, ?float $min = null, ?float $max = null, ?string $column = null): array
    {
        return [
            'type' => 'number',
            'label' => $label,
            'min' => $min,
            'max' => $max,
            'column' => $column,
        ];
    }

    /**
     * Generate filter data for a number range (min - max).
     *
     * @param string $label
     * @param float|null $min Minimum value
     * @param float|null $max Maximum value
     * @param string|null $column Database column name (defaults to filter key)
     * @return array
     */
    public static function range(string $label, ?float $min = null, ?float $max = null, ?string $column = null): array
    {
        return [
            'type' => 'range',
            'label' => $label,
            'min' => $min,
            'max' => $max,
            'column' => $column,
        ];
    }

    /**
     * Generate filter data for a boolean/checkbox input.
     *
     * @param string $label
     * @param string|null $column Database column name (defaults to filter key)
     * @return array
     */
    public static function boolean(string $label, ?string $column = null): array
    {
        return [
            'type' => 'boolean',
            'label' => $label,
            'options' => [
                '1' => 'Yes',
                '0' => 'No',
            ],
            'column' => $column,
        ];
    }

    /**
     * Generate filter data for a select dropdown with custom query callback.
     * Use this when you need custom query logic based on the selected value.
     *
     * @param string $label
     * @param array $options Key-value pairs
     * @param callable $callback Callback function that receives ($query, $value) and applies custom query
     * @return array
     *
     * @example
     * Filter::selectCustom('Has Profile', [
     *     'investor' => 'Has Investor Profile',
     *     'owner' => 'Has Owner Profile',
     *     'both' => 'Has Both Profiles',
     *     'none' => 'No Profiles'
     * ], function ($query, $value) {
     *     switch ($value) {
     *         case 'investor':
     *             $query->whereHas('investorProfile');
     *             break;
     *         case 'owner':
     *             $query->whereHas('ownerProfile');
     *             break;
     *         case 'both':
     *             $query->whereHas('investorProfile')->whereHas('ownerProfile');
     *             break;
     *         case 'none':
     *             $query->whereDoesntHave('investorProfile')->whereDoesntHave('ownerProfile');
     *             break;
     *     }
     * })
     */
    public static function selectCustom(string $label, array $options, callable $callback): array
    {
        return [
            'type' => 'select-custom',
            'label' => $label,
            'options' => $options,
            'callback' => $callback,
        ];
    }
}
