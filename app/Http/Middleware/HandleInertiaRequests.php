<?php
/*
 * GorKa Team
 * Copyright (c) 2024  Vlad Horpynych <19dynamo27@gmail.com>, Pavel Karpushevskiy <pkarpushevskiy@gmail.com>
 */

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;

/**
 * Handle Inertia Requests
 *
 * @package \App\Http\Middleware
 */
class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request) : string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request) : array
    {
        $shared = [
            'auth'   => [
                'canLogin'         => Route::has('auth.login'),
                'canRegister'      => Route::has('auth.register'),
                'canResetPassword' => Route::has('password.reset'),
                'user'             => optional($request->user())->toArray(),
            ],
            'status' => session()->get('status'),
            'ziggy'  => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },
        ];

        $additionalData = session()->get('additional_data');

        if (!empty($additionalData)) {
            $shared['additionalData'] = !is_array($additionalData) ? [$additionalData] : $additionalData;
        }

        return array_merge(parent::share($request), $shared);
    }
}
