<?php
/*
 * GorKa Team
 * Copyright (c) 2024  Vlad Horpynych <19dynamo27@gmail.com>, Pavel Karpushevskiy <pkarpushevskiy@gmail.com>
 */

namespace App\Http\Controllers;

use App\Http\Requests\Users\CreateUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Resources\BaseInertiaResourceCollection;
use App\Interfaces\FileGenerators\PdfGeneratorServiceInterface;
use App\Models\User;
use App\Services\Items\RoleItemService;
use App\Services\Items\UserItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Users Controller
 *
 * @package \App\Http\Controllers
 */
class UsersController extends BaseController
{
    /**
     * @var UserItemService
     */
    protected UserItemService $userItemService;

    /**
     * @var RoleItemService
     */
    protected RoleItemService $roleItemService;

    /**
     * UsersController constructor.
     *
     * @param UserItemService $userItemService
     * @param RoleItemService $roleItemService
     * @return void
     */
    public function __construct(UserItemService $userItemService, RoleItemService $roleItemService)
    {
        $this->userItemService = $userItemService;
        $this->roleItemService = $roleItemService;
    }

    /**
     * @return RedirectResponse
     */
    protected function accessDeniedResponse() : RedirectResponse
    {
        return Redirect::route('users.index')
            ->withErrors(__('exceptions.user_does_not_have_access'));
    }

    /**
     * @param Request $request
     * @return JsonResponse|InertiaResponse
     */
    public function index(Request $request)
    {
        $this->authorize('authorizeAdminAccess', User::class);

        $currentUser = $request->user();
        $args        = $request->only(['page', 'per_page', 'sort', 'order_by', 'full_name', 'email']);

        if (!empty($args['page'])) {
            $usersFilters = ['paginated' => true];
            $rolesFilters = [];

            if ($currentUser->is_admin) {
//            $usersFilters['withoutRoles'] = [config('permission.project_roles.super_admin'), config('permission.project_roles.admin')];
                $rolesFilters['exclude_name'] = [config('permission.project_roles.super_admin'), config('permission.project_roles.admin')];
            }

            $result = $this->userItemService->collection(array_merge($args, $usersFilters));

            return Redirect::back()->withAdditionalData(array_merge(BaseInertiaResourceCollection::make($result)->resolve(), [
                'roles'   => $this->roleItemService->collection($rolesFilters),
                'filters' => $request->only(['full_name', 'email']),
            ]));
        } else {
            return Inertia::render('Users/Users');
        }
    }

    /**
     * @param User $user
     * @return InertiaResponse
     */
    public function show(User $user) : InertiaResponse
    {
        $this->authorize('authorizeAdminAccess', User::class);

        return Inertia::render('Users/ShowUser', [
            'user' => $user,
        ]);
    }

    /**
     * @param Request $request
     * @param User    $user
     * @return RedirectResponse|InertiaResponse
     */
    public function edit(Request $request, User $user) : RedirectResponse|InertiaResponse
    {
        $this->authorize('authorizeAdminAccess', User::class);

        $currentUser = $request->user();

        if (
            $currentUser->hasRole(config('permission.project_roles.admin')) &&
            $currentUser->id !== $user->id &&
            $user->hasAnyRole([config('permission.project_roles.super_admin'), config('permission.project_roles.admin')])
        ) {
            return $this->accessDeniedResponse();
        }

        $rolesFilters = [];

        if ($currentUser->is_admin) {
            $rolesFilters = [
                'exclude_name' => [config('permission.project_roles.super_admin'), config('permission.project_roles.admin')],
            ];
        }

        return Inertia::render('Users/ManageUser', [
            'user'  => $user,
            'roles' => $this->roleItemService->collection($rolesFilters),
        ]);
    }

    /**
     * @param CreateUserRequest $request
     * @return RedirectResponse
     */
    public function store(CreateUserRequest $request) : RedirectResponse
    {
        $this->authorize('authorizeAdminAccess', User::class);

        $attributes = $request->validated();
        $result     = $this->userItemService->create($attributes);

        return $result
            ? Redirect::back()->withStatus(__('crud.users.create_success'))
            : Redirect::back()->withErrors(__('crud.users.create_error'));
    }

    /**
     * @param UpdateUserRequest $request
     * @param User              $user
     * @return RedirectResponse
     */
    public function update(UpdateUserRequest $request, User $user) : RedirectResponse
    {
        $this->authorize('authorizeAdminAccess', User::class);

        $currentUser = $request->user();

        if (
            $currentUser->hasRole(config('permission.project_roles.admin')) &&
            $currentUser->id !== $user->id &&
            $user->hasAnyRole([config('permission.project_roles.super_admin'), config('permission.project_roles.admin')])
        ) {
            return $this->accessDeniedResponse();
        }

        $attributes = $request->validated();
        $result     = $this->userItemService->update($user->id, $attributes);

        return $result
            ? Redirect::back()->withStatus(__('crud.users.update_success'))
            : Redirect::back()->withErrors(__('crud.users.update_error'));
    }

    /**
     * @param Request $request
     * @param User    $user
     * @return RedirectResponse
     */
    public function destroy(Request $request, User $user) : RedirectResponse
    {
        $this->authorize('authorizeAdminAccess', User::class);

        $currentUser = $request->user();

        if (
            $currentUser->hasRole(config('permission.project_roles.admin')) &&
            $currentUser->id === $user->id &&
            $user->hasAnyRole([config('permission.project_roles.super_admin'), config('permission.project_roles.admin')])
        ) {
            return $this->accessDeniedResponse();
        }

        $result = $this->userItemService->delete($user->id);

        return $result
            ? Redirect::back()->withStatus(__('crud.users.delete_success'))
            : Redirect::back()->withErrors(__('crud.users.delete_error'));
    }

    /**
     * @param Request $request
     * @param User    $user
     * @return RedirectResponse
     */
    public function restore(Request $request, User $user) : RedirectResponse
    {
        $this->authorize('authorizeAdminAccess', User::class);

        $currentUser = $request->user();

        if (
            $currentUser->hasRole(config('permission.project_roles.admin')) &&
            $currentUser->id !== $user->id &&
            $user->hasAnyRole([config('permission.project_roles.super_admin'), config('permission.project_roles.admin')])
        ) {
            return $this->accessDeniedResponse();
        }

        $result = $this->userItemService->restore($user->id);

        return $result
            ? Redirect::back()->withStatus(__('crud.users.restore_success'))
            : Redirect::back()->withErrors(__('crud.users.restore_error'));
    }

    /**
     * @param Request $request
     * @param User    $user
     * @return RedirectResponse|BinaryFileResponse
     */
    public function download(Request $request, User $user) : RedirectResponse|BinaryFileResponse
    {
        $this->authorize('authorizeAdminAccess', User::class);

        $pdfGeneratorService = app(PdfGeneratorServiceInterface::class);

        $data = $pdfGeneratorService->createFromBlade(
            'file-templates.pdf.user-info-template',
            [
                'user' => $user,
            ],
            [
                'file_name'   => 'user_info_pdf',
                'header-html' => view('layouts.pdf-components.pdf-file-spatie-header', [
                    'headerData' => [
                        'current_time' => Carbon::now()->format('Y-m-d H:i e'),
                    ],
                ])->render(),
                'footer-html' => view('layouts.pdf-components.pdf-file-spatie-footer', [
                    [
                        'display_document_meta' => true,
                    ],
                ])->render(),
            ],
            false
        );

        return $data ?
            Response::download($data, basename($data), [
                'Content-Type'        => mime_content_type($data),
                'Content-Disposition' => 'attachment; filename="' . basename($data) . '"',
            ])
            : Redirect::back()->withErrors(__('crud.users.download_error'));
    }
}
