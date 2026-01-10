<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Razorpay Credentials Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the Razorpay API credentials organized by account type.
    | Each account is used for specific branch groups to separate payment flows.
    |
    | Branch mapping is based on the legacy Core PHP application logic.
    |
    */

    'accounts' => [
        /**
         * KUKU EXAM Account
         * Used for branches: 68, 34, 67, 32, 35, 74
         */
        'kuku_exam' => [
            'key_id' => env('RAZORPAY_KUKU_KEY_ID', 'rzp_live_pTWAP0kjfa1vE8'),
            'key_secret' => env('RAZORPAY_KUKU_KEY_SECRET', 'huA2LerjpYxROcvJgNzJptiP'),
            'branches' => [68, 34, 67, 32, 35, 74],
        ],

        /**
         * Yogoju Event Account
         * Used for branches: 39, 72, 28, 71, 42, 73, 38, 70, 43, 31, 75, 27, 51, 56, 82, 90
         */
        'yogoju_event' => [
            'key_id' => env('RAZORPAY_YOGOJU_KEY_ID', 'rzp_live_aCvUQkKVnFxtrW'),
            'key_secret' => env('RAZORPAY_YOGOJU_KEY_SECRET', '8Stz6IasX2j9jUDUTKg11wPU'),
            'branches' => [39, 72, 28, 71, 42, 73, 38, 70, 43, 31, 75, 27, 51, 56, 82, 90],
        ],

        /**
         * RKKF Fee Account
         * Used for branches: 66, 64, 29, 69, 41, 78, 30, 80, 26, 84, 53, 85, 65, 77, 33, 81, 37, 76, 83
         */
        'rkkf_fee' => [
            'key_id' => env('RAZORPAY_RKKF_KEY_ID', 'rzp_live_H9VcMjuwzC0Aix'),
            'key_secret' => env('RAZORPAY_RKKF_KEY_SECRET', '2lGJI6c4UMLeYuBnIBnINd2X'),
            'branches' => [66, 64, 29, 69, 41, 78, 30, 80, 26, 84, 53, 85, 65, 77, 33, 81, 37, 76, 83],
        ],

        /**
         * RF Sales (Default) Account
         * Used as fallback for branches not in other groups, and for product/merchandise payments.
         */
        'rf_sales' => [
            'key_id' => env('RAZORPAY_DEFAULT_KEY_ID', 'rzp_live_bCTOrVy6sjvk7b'),
            'key_secret' => env('RAZORPAY_DEFAULT_KEY_SECRET', 'q89Hvvlc5qLi623yh6pMDG84'),
            'branches' => [], // Default account - no specific branches
        ],
    ],

    /**
     * Default account to use when no branch mapping is found
     */
    'default_account' => 'rf_sales',

    /**
     * Currency for all transactions
     */
    'currency' => 'INR',

    /**
     * Webhook secret for signature verification
     * This should match what is configured in your Razorpay Dashboard
     */
    'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET', ''),
];
