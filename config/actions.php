<?php
/*
 * GorKa Team
 * Copyright (c) 2024  Vlad Horpynych <19dynamo27@gmail.com>, Pavel Karpushevskiy <pkarpushevskiy@gmail.com>
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Actions Repository Connection
    |--------------------------------------------------------------------------
    |
    | This option controls the database connection used to store the table
    | of executed actions.
    |
    */

    'connection' => env('DB_CONNECTION'),

    /*
    |--------------------------------------------------------------------------
    | Actions Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the actions that have already run for
    | your application. Using this information, we can determine which of
    | the actions on disk haven't actually been run in the database.
    |
    */

    'table' => 'actions',

    /*
    |--------------------------------------------------------------------------
    | Actions Path
    |--------------------------------------------------------------------------
    |
    | This option defines the path to the action directory.
    |
    */

    'path' => database_path('actions'),

    /*
    |--------------------------------------------------------------------------
    | Path Exclusion
    |--------------------------------------------------------------------------
    |
    | This option determines which directory and/or file paths should be
    | excluded when processing files.
    |
    | Valid values: array, string or null
    |
    | Specify `null` to disable.
    |
    | For example,
    |    ['foo', 'bar']
    |    'foo'
    |    null
    |
    */

    'exclude' => null,

];
