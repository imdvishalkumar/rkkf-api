<?php

/**
 * Branch Groups Configuration
 * 
 * This file replaces hard-coded branch ID arrays found in:
 * - enquire/new_form.php
 * - api/v2/payment_v2/get_order_id.php
 * 
 * Move all hard-coded branch arrays to this config file.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Branch Groups
    |--------------------------------------------------------------------------
    |
    | Define groups of branches for different purposes.
    | This replaces hard-coded branch ID arrays in the codebase.
    |
    */
    'groups' => [
        'enquiry_branches' => [
            66, 69, 38, 43, 60, 70, 86, 29, 28, 64, 71, 39, 72, 42, 73, 
            31, 75, 37, 76, 65, 77, 41, 78, 32, 67, 34, 68, 25, 83
        ],

        'kuku_exam_branches' => [
            68, 34, 67, 32, 35, 74
        ],

        'yogoju_event_branches' => [
            39, 72, 28, 71, 42, 73, 38, 70, 43, 31, 75, 27, 51, 56, 82, 90
        ],

        'rkkf_fee_branches' => [
            66, 64, 29, 69, 41, 78, 30, 80, 26, 84, 53, 85, 65, 77, 33, 81, 37, 76, 83
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Branches
    |--------------------------------------------------------------------------
    |
    | Branches that should be excluded from certain operations.
    |
    */
    'excluded' => [
        'enquiry' => [40, 44, 45, 46, 47, 48, 49, 50, 52, 53, 54],
        'rkkf_fee' => [86, 84, 85],
    ],

    /*
    |--------------------------------------------------------------------------
    | Helper Functions
    |--------------------------------------------------------------------------
    |
    | Use these helper functions in your code:
    | 
    | config('branch_groups.groups.enquiry_branches')
    | config('branch_groups.groups.kuku_exam_branches')
    | 
    | Or create a helper service:
    | BranchGroupService::getBranchesForEnquiry()
    | BranchGroupService::getBranchesForKukuExam()
    |
    */
];



