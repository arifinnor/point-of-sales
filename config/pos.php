<?php

return [

    /*
    |--------------------------------------------------------------------------
    | POS Business Rules Configuration
    |--------------------------------------------------------------------------
    |
    | These values define the business rules and constraints for your POS system.
    | Modify these values to adjust limits without changing code.
    |
    */

    'constraints' => [
        
        /*
        |--------------------------------------------------------------------------
        | Cashier Constraints
        |--------------------------------------------------------------------------
        */
        'cashier' => [
            'max_return_amount' => env('POS_CASHIER_MAX_RETURN', 1000000), // Rp1,000,000
        ],

        /*
        |--------------------------------------------------------------------------
        | Supervisor Constraints  
        |--------------------------------------------------------------------------
        */
        'supervisor' => [
            'max_stock_adjustment' => env('POS_SUPERVISOR_MAX_STOCK_ADJUSTMENT', 5), // ±5 units
        ],

        /*
        |--------------------------------------------------------------------------
        | Transaction Approval Thresholds
        |--------------------------------------------------------------------------
        */
        'approval' => [
            'supervisor_required_amount' => env('POS_SUPERVISOR_APPROVAL_THRESHOLD', 5000000), // Rp5,000,000
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Business Hours Configuration
    |--------------------------------------------------------------------------
    */
    'business_hours' => [
        'start' => env('POS_BUSINESS_HOURS_START', 8), // 8 AM
        'end' => env('POS_BUSINESS_HOURS_END', 22), // 10 PM
        'timezone' => env('POS_TIMEZONE', 'Asia/Jakarta'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency Configuration
    |--------------------------------------------------------------------------
    */
    'currency' => [
        'code' => env('POS_CURRENCY_CODE', 'IDR'),
        'symbol' => env('POS_CURRENCY_SYMBOL', 'Rp'),
        'decimal_places' => env('POS_CURRENCY_DECIMAL_PLACES', 0), // IDR typically no decimals
        'cash_rounding' => env('POS_CASH_ROUNDING', 100), // Round to nearest Rp100
    ],

    /*
    |--------------------------------------------------------------------------
    | Receipt Configuration
    |--------------------------------------------------------------------------
    */
    'receipt' => [
        'number_format' => env('POS_RECEIPT_FORMAT', '{outlet}/{register}-{date}-{number}'),
        'daily_reset' => env('POS_RECEIPT_DAILY_RESET', true),
        'number_padding' => env('POS_RECEIPT_NUMBER_PADDING', 4), // Zero-pad to 4 digits
    ],

    /*
    |--------------------------------------------------------------------------
    | Inventory Configuration
    |--------------------------------------------------------------------------
    */
    'inventory' => [
        'allow_negative_stock' => env('POS_ALLOW_NEGATIVE_STOCK', false),
        'low_stock_threshold' => env('POS_LOW_STOCK_THRESHOLD', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tax Configuration
    |--------------------------------------------------------------------------
    */
    'tax' => [
        'default_rate' => env('POS_DEFAULT_TAX_RATE', 0.11), // 11% VAT in Indonesia
        'price_includes_tax' => env('POS_PRICE_INCLUDES_TAX', true),
        'rounding_method' => env('POS_TAX_ROUNDING_METHOD', 'bankers'), // bankers|round|ceil|floor
    ],

    /*
    |--------------------------------------------------------------------------
    | Shift Configuration
    |--------------------------------------------------------------------------
    */
    'shifts' => [
        'require_opening_float' => env('POS_REQUIRE_OPENING_FLOAT', true),
        'auto_close_hours' => env('POS_AUTO_CLOSE_SHIFT_HOURS', 24), // Auto-close after 24 hours
        'cash_variance_threshold' => env('POS_CASH_VARIANCE_THRESHOLD', 10000), // Rp10,000 variance warning
    ],

    /*
    |--------------------------------------------------------------------------
    | Discount Configuration
    |--------------------------------------------------------------------------
    */
    'discounts' => [
        'max_percentage' => env('POS_MAX_DISCOUNT_PERCENTAGE', 100),
        'require_approval_threshold' => env('POS_DISCOUNT_APPROVAL_THRESHOLD', 50), // 50% discount requires approval
    ],

];
