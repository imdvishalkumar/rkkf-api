<?php

/**
 * Role Configuration
 * 
 * This file contains role-related configuration including
 * special user emails and their redirect routes.
 * 
 * Move hard-coded email checks from login.php to here.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Role Definitions
    |--------------------------------------------------------------------------
    |
    | Define role constants to replace magic numbers.
    |
    */
    'roles' => [
        'admin' => 1,
        'instructor' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | Special User Emails
    |--------------------------------------------------------------------------
    |
    | Emails that have special redirect behavior after login.
    | These should ideally be moved to database with a 'redirect_route' field.
    |
    */
    'special_users' => [
        'savvyswaraj@gmail.com' => [
            'session_key' => 'club',
            'redirect_route' => 'club.view.fees',
            'description' => 'Club View Access',
        ],
        'tmc@gmail.com' => [
            'session_key' => 'tmc',
            'redirect_route' => 'tmc.view.fees',
            'description' => 'TMC View Access',
        ],
        'baroda@gmail.com' => [
            'session_key' => 'baroda',
            'redirect_route' => 'baroda.view.fees',
            'description' => 'Baroda View Access',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Redirect Routes
    |--------------------------------------------------------------------------
    |
    | Default routes for different roles after login.
    |
    */
    'default_routes' => [
        'admin' => 'dashboard',
        'instructor' => 'dashboard',
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Labels
    |--------------------------------------------------------------------------
    |
    | Human-readable labels for roles.
    |
    */
    'labels' => [
        1 => 'Admin',
        2 => 'Instructor',
    ],
];



